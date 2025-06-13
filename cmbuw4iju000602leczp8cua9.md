---
title: "PHP ORM Wrapup and Benchmarks"
datePublished: Fri Jun 13 2025 14:15:59 GMT+0000 (Coordinated Universal Time)
cuid: cmbuw4iju000602leczp8cua9
slug: php-orm-wrapup-and-benchmarks
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/ZRbZq5Se3Os/upload/e65c8f9e9b9b46a3886bcec47f1f5784.jpeg
tags: php, sql, benchmark, orm, benchmarking

---

In my past columns I have covered the design and implementation of a PHP ORM. Let’s recap:

## ORM Design Goals

* Active Record functionality including:
    
    * CRUD functionality
        
    * Field names match table column names
        
    * Fields typed like table columns
        
    * Fields type checked
        
    * Field validation with understandable error messages
        
    * Fields with PHP class types (like Carbon)
        
    * Virtual Fields
        
    * Relationships:
        
        * Parent Record
            
        * Child Records
            
        * One To One
            
        * Many To Many
            
    * Per class custom logic
        
* Database Cursors
    
* Active Table functionality including:
    
    * SELECT
        
    * UPDATE
        
    * DELETE
        
    * INSERT
        
    * WHERE and HAVING clauses
        
    * LIMIT clause
        
    * ORDER BY clause
        
    * JOINs
        
    * UNIONs
        
* Support for plain text SQL queries
    
* Atomic migrations
    
* Auto generation and updating of classes
    
* Fast and small memory footprint
    

## Mission Accomplished!

If you have been following along with this blog, you will see I covered most of these topics and have implemented all of them in less than 7K lines of OO PHP code in 51 files. You can see the [final version on GitHub](https://github.com/phpfui/orm). But the real test is performance. Does my ORM consume large amounts of memory or perform poorly? For this I decided to perform some benchmarks of my ORM and other commonly used ORM implementations. Let us see how they did.

## PHP ORM and SQL Benchmarks

As you might expect, there are very few open source PHP ORM benchmarks available. I did find several, but they were either no longer maintained or not sufficiently configurable. So I decided to write my own. Not only can we benchmark PHP ORMs, but we can also benchmark various SQL servers and implementations depending on what each ORM supports. One thing I did not do is use some sort of virtual machine. The idea is to benchmark against the actual machine that is running the code and compare ORM and SQL server performance on the same physical machine. No need for any abstraction, we want the same base for a comparison. I am sure other hardware platforms may be faster, but by testing everything on the same hardware, we can factor out the hardware and look at just the software performance.

Another issue with synthetic benchmarks is what exactly are you trying to benchmark? A simple CRUD website, or a longer process that updates thousands of records for each run? For a typical website that deals with individual users (most websites), you are looking for two things, one is how fast you can respond to a specific user, and the other is how many users you can serve at one time. The first requires a quick compute light response, the second means you can’t place a high memory load on a server, or you will reduce the number of concurrent users you can support. But if you are doing back end batch processing, you are probably worried about updating massive numbers of records efficiently.

For the type of websites I make, I want a quick response time and a minimal memory footprint, as I want to serve the user quickly, and I want to make sure I can serve a bunch of users at the same time. And each user will only be updating a few records for any one request. I am not processing huge amounts of data in the background, and if I do, it is jobs that might run once a day and are not user facing, so overall efficiency is not a major concern.

I designed my ORM for my needs after seeing some of the performance and design flaws of mainstream ORMs like Eloquent and Doctrine. Let’s see how my ORM stacks up against the PHP heavyweights:

## And the Winner is ….

First a bit about the tests. I did one iteration and 1000 iterations. Each iteration inserts a number of (1-X) records, then updates them, then reads them to make sure they are correct, then deletes them. If you just run the test once, it is actually a good simulation of a typical web request where a user hits a page, makes a small update and is done. If you increase the number of iterations to 1000 or more, you start to see how the ORM (and database) responds to bigger backend jobs. My expectations where that my ORM would perform well for single record use cases, as that was my original goal, but I was pleasantly surprised!

**Here are the results for overall time for the single record test:**

| Test | Description | Total Runtime Time |
| --- | --- | --- |
| PHPFUI | sqlite::memory: | 0.005319 |
| PHPFUI | MariaDB | 0.013865 |
| PHPFUI | MySQL | 0.014089 |
| PHPFUI | sqlite::file: | 0.023925 |
| CakeCached | sqlite::memory: | 0.073497 |
| Cake | sqlite::memory: | 0.079643 |
| Eloquent | sqlite::memory: | 0.083258 |
| CakeCached | sqlite::file: | 0.088872 |
| CakeCached | MariaDB | 0.089757 |
| Eloquent | MariaDB | 0.095263 |
| Doctrine | MariaDB | 0.096415 |
| Eloquent | sqlite::file: | 0.097228 |
| Doctrine | sqlite::file: | 0.097283 |
| Cake | sqlite::file: | 0.099509 |
| Doctrine | sqlite::memory: | 0.107832 |
| Eloquent | MySQL | 0.114336 |
| CakeCached | MySQL | 0.114883 |
| Cake | MySQL | 0.154735 |
| Doctrine | MySQL | 0.15571 |
| Cake | MariaDB | 0.158851 |

**But what about memory usage? Remember I need to keep that minimal to serve lots of users:**

| Test | Description | Total Runtime Memory |
| --- | --- | --- |
| PHPFUI | sqlite::memory: | 254864 |
| PHPFUI | sqlite::file: | 254872 |
| PHPFUI | MySQL | 300904 |
| PHPFUI | MariaDB | 300912 |
| CakeCached | MySQL | 3246952 |
| CakeCached | MariaDB | 3247000 |
| CakeCached | sqlite::memory: | 3280632 |
| CakeCached | sqlite::file: | 3280632 |
| Cake | MySQL | 3404000 |
| Cake | MariaDB | 3404048 |
| Cake | sqlite::memory: | 3431560 |
| Cake | sqlite::file: | 3431560 |
| Doctrine | sqlite::file: | 3607224 |
| Doctrine | sqlite::memory: | 3607224 |
| Doctrine | MySQL | 3709696 |
| Doctrine | MariaDB | 3735344 |
| Eloquent | sqlite::memory: | 4503160 |
| Eloquent | sqlite::file: | 4503200 |
| Eloquent | MySQL | 4557040 |
| Eloquent | MariaDB | 4557048 |

**And how about larger jobs? Here are the time results from 1000 iterations:**

| Test | Description | Total Runtime Time |
| --- | --- | --- |
| PHPFUI | sqlite::memory: | 0.343638 |
| Eloquent | sqlite::memory: | 1.587358 |
| PHPFUI | MariaDB | 2.247993 |
| CakeCached | sqlite::memory: | 2.363525 |
| PHPFUI | MySQL | 2.487758 |
| Cake | sqlite::memory: | 4.908919 |
| Eloquent | MariaDB | 5.275634 |
| CakeCached | MariaDB | 5.822379 |
| Eloquent | MySQL | 5.943731 |
| CakeCached | MySQL | 7.146089 |
| CakeCached | sqlite::file: | 27.6228 |
| Cake | sqlite::file: | 31.33709 |
| Doctrine | sqlite::memory: | 34.44359 |
| Doctrine | sqlite::file: | 35.26883 |
| Doctrine | MariaDB | 38.41596 |
| Doctrine | MySQL | 38.71112 |
| PHPFUI | sqlite::file: | 44.60464 |
| Eloquent | sqlite::file: | 45.02157 |
| Cake | MySQL | 50.43843 |
| Cake | MariaDB | 55.88082 |

**And memory usage for 1000 users:**

| Test | Description | Total Runtime Memory |
| --- | --- | --- |
| PHPFUI | sqlite::memory: | 254864 |
| PHPFUI | sqlite::file: | 254872 |
| PHPFUI | MySQL | 300904 |
| PHPFUI | MariaDB | 300912 |
| CakeCached | MySQL | 3246952 |
| CakeCached | MariaDB | 3247000 |
| CakeCached | sqlite::memory: | 3280632 |
| CakeCached | sqlite::file: | 3280632 |
| Doctrine | sqlite::memory: | 3757656 |
| Doctrine | sqlite::file: | 3757656 |
| Doctrine | MySQL | 3860128 |
| Doctrine | MariaDB | 3885776 |
| Eloquent | sqlite::memory: | 4503160 |
| Eloquent | sqlite::file: | 4503200 |
| Eloquent | MySQL | 4557072 |
| Eloquent | MariaDB | 4557080 |
| Cake | sqlite::memory: | 7242432 |
| Cake | sqlite::file: | 7242432 |
| Cake | MySQL | 7485376 |
| Cake | MariaDB | 7485424 |

## Takeaways

As you can see, my PHPFUI/ORM outperforms all the other ORMs. Not surprising to me, as I know all these are bloated and slow ORMs from personal experience. Notice the memory requirements of other ORMs are between 10 and 17 times my ORM. This is a major cause of excessive hosting costs, as you need many more machines with lots of memory to handle the the same number of requests.

For the single iteration (the closest test we have to a typical web page request), the best performing ORM was 13 times slower than my ORM on the same database (SQLite memory). Also notice that my ORM performed best for all SQL server based implementations.

## Test Things Yourself!

The benchmark suite is open source and available here: [https://github.com/phpfui/php-orm-sql-benchmarks](https://github.com/phpfui/php-orm-sql-benchmarks) PR’s welcome if you see an issue or want to add an ORM.

Here is the **config.php** file I used to save you some time setting up tests:

```php
return [
	'iterations' => 1, // default is 5000
	'tests' => [
		['namespace' => 'Cake', 'description' => 'sqlite::memory:', 'dbname' => ':memory:'],
		['namespace' => 'Cake', 'description' => 'sqlite::file:', 'dbname' => 'cake.sqlite'],
		['namespace' => 'Cake', 'driver' => 'mysql', 'description' => 'MySQL'],
		['namespace' => 'Cake', 'driver' => 'mysql', 'description' => 'MariaDB', 'port' => 3307],
		['namespace' => 'CakeCached', 'description' => 'sqlite::memory:', 'dbname' => ':memory:'],
		['namespace' => 'CakeCached', 'description' => 'sqlite::file:', 'dbname' => 'cakecached.sqlite'],
		['namespace' => 'CakeCached', 'driver' => 'mysql', 'description' => 'MySQL'],
		['namespace' => 'CakeCached', 'driver' => 'mysql', 'description' => 'MariaDB', 'port' => 3307],
		['namespace' => 'Doctrine', 'description' => 'sqlite::memory:', 'dbname' => ':memory:'],
		['namespace' => 'Doctrine', 'description' => 'sqlite::file:', 'dbname' => 'doctrine.sqlite'],
		['namespace' => 'Doctrine', 'driver' => 'mysql', 'description' => 'MySQL'],
		['namespace' => 'Doctrine', 'driver' => 'mysql', 'description' => 'MariaDB', 'port' => 3307],
		['namespace' => 'Eloquent', 'description' => 'sqlite::memory:', 'dbname' => ':memory:'],
		['namespace' => 'Eloquent', 'description' => 'sqlite::file:', 'dbname' => 'eloquent.sqlite'],
		['namespace' => 'Eloquent', 'driver' => 'mysql', 'description' => 'MySQL'],
		['namespace' => 'Eloquent', 'driver' => 'mysql', 'description' => 'MariaDB', 'port' => 3307],
		['namespace' => 'PHPFUI', 'description' => 'sqlite::memory:', 'dbname' => ':memory:'],
		['namespace' => 'PHPFUI', 'description' => 'sqlite::file:', 'dbname' => 'phpfui.sqlite'],
		['namespace' => 'PHPFUI', 'driver' => 'mysql', 'description' => 'MySQL'],
		['namespace' => 'PHPFUI', 'driver' => 'mysql', 'description' => 'MariaDB', 'port' => 3307],
	],
];
```

The suite allows you to run all benchmarks consecutively in one session, but I would not recommend that for anything other than testing new configurations as each benchmark can influence the other benchmarks. Instead create a script that calls each benchmark individually by passing the index number of the test as a parameter:

```plaintext
php benchmark.php 0
php benchmark.php 1
php benchmark.php 2
```

**PREVIOUS: -** [**ORM Record Validation in PHP**](https://blog.phpfui.com/orm-record-validation-in-php)