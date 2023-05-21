---
title: "Zen and the art of class design"
datePublished: Sun May 21 2023 21:44:02 GMT+0000 (Coordinated Universal Time)
cuid: clhxy4eyk00020al76wyh347y
slug: zen-and-the-art-of-class-design
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/FIKD9t5_5zQ/upload/92659bd275db05b8d5ad6740d8da0fb3.jpeg
tags: design, php, classes, object-oriented-programming

---

> **zen** : a state of calm attentiveness in which one's actions are guided by intuition rather than by conscious effort - **Merrian Webster**

In my previous posts, I discussed class design and naming conventions, so I thought I would go into more depth on those subjects and see how it applies to real-world applications. I will be using the concept of **zen** and not having to think about details that get in the way of what I am trying to accomplish, which is creating websites.

### Functional vs Object Oriented

With a functional interface, such as the [original PHP MySQL interface](https://www.php.net/manual/en/book.mysql.php), you end up with huge amounts of boilerplate. For example to read a row out of a table (assuming you already have an open database connection):

```php
// Select the row
$sql = "SELECT * FROM table WHERE id = 1";
// Execute the query
$result = mysql_query($sql);
// Fetch the row
$row = mysql_fetch_assoc($result);
// Print the row
echo $row["column1"] . " " . $row["column2"] . " " . $row["column3"];
```

This is not zen. We are having to deal with things at a very low level and I am concentrating on the work involved in retrieving things from the database rather than my website logic.

### MySQLi Improved?

With the [improved MySQLi interface](https://www.php.net/manual/en/book.mysqli.php), you still end up with a lot of code just to save or retrieve something. Even the "OO" version of the interface still leaves you with a lot of boilerplate.

```php
$mysqli = new mysqli('localhost', 'my_user', 'my_password', 'world');
// Create the SQL query
$sql = "SELECT * FROM table WHERE id = 1";
// Execute the SQL query
$result = $mysqli->query($sql);
// Fetch the row from the result set
$row = $result->fetch_assoc();
// Print the row data
echo $row['column1'] . ' ' . $row['column2'] . ' ' . $row['column3'];
```

The "big" OO improvement here is the mysqli object ($mysqli) allows you to associate a specific instance of a database to a specific query. It still has the same level of boilerplate, just substituting OO semantics for the previous functional interface. Still not zen, too much to think about.

One of the biggest problems with any SQL interface is [SQL injection attacks](https://www.php.net/manual/en/security.database.sql-injection.php) where passing unescaped user data could be manipulated into running untrusted code. Things get more complicated once you have to accommodate user data, which is most of the time:

```php
$mysqli = new mysqli('localhost', 'my_user', 'my_password', 'world');
$stmt = $mysqli->prepare("SELECT Language FROM CountryLanguage WHERE CountryCode IN (?, ?)");
$stmt->bind_param('ss', 'DEU', 'POL');
$stmt->execute();
$stmt->store_result();
echo $stmt->num_rows() . " rows found.\n";
```

So while MySQLi improved things, it is still not a nice abstraction or encapsulation of SQL query functionality, and still not zen. While it is safe from SQL injection attacks, we are even further away from the idea of zen, since we have to deal with even more boilerplate.

### Enter PDO

With PHP 5.1, the PDO class was introduced to try to make things even easier and a bit more generic.

```php
$pdo = new PDO('mysql:dbname=world;host=localhost', 'my_user', 'my_password');
$stm = $pdo->prepare('SELECT name, colour, calories FROM fruit WHERE calories < ? AND colour = ?');
$stm->execute([150, 'red']);
$result = $stm->fetchAll();
foreach ($result as $row)
  echo "{$row['name'] has {$row['calories']} calories and is {$row['colour']\n";
```

We are still dealing with arrays as result sets, but clearly, we are dealing with less boilerplate code, but still no abstraction and encapsulation. It does not seem very zen, but better than the mysqli interface.

### Pure OO Abstraction and Encapsulation

One of the goals of OO (object-oriented) design is to encapsulate implementation details so you can concentrate on your business logic and not have to deal with pesky details that every application has to deal with, like saving and retrieving previously stored data.

At a high abstract level, you simply want to have an object you are working with to be able to save, load, update or even delete itself. It seems these are basic properties you would want on any object. So how do we get there?

### Designing A Class

When we talk about designing a class, we want to think about want we are trying to accomplish. In this case, we are talking about storing and retrieving records from a SQL database. So we want our object to represent the data structure of the table. The easiest way to think about this is for each column of the table to have a corresponding representation in our object. Ideally, we would name each field of the object to be the same as the table column names. We then have a clear one-to-one mapping of the record to the table and no room for interpretation.

### Define Requirements

We previously determined we need basic CRUD (Create, Read, Update and Delete) functionality. This is a known design pattern called [Active Record](https://en.wikipedia.org/wiki/Active_record_pattern).

But what else might we want?

### Requirements vs Nice To Have

One of the biggest decisions in class design is to know what is required of the class, and what would be nice to have. I tend to like to keep things minimal, so I only add nice-to-have things if they can be easily and trivially implemented. But the problem with class design is you want to make it flexible enough that you can accommodate new requirements without breaking older functionality. The best way to do this is to do some blue-sky thinking and see what you can do cheaply and efficiently.

### Blue Sky Thinking

Let's dream a bit about what we might want and see what makes sense. Certainly, we would want to know what we are inserting is valid, so we want some sort of record validation.

Maybe we would also want to ensure data consistency for any data we insert into our table. Two examples would be US state abbreviations and zip codes. Both of these have strict known formats.

We would also want to be able to add some record-specific code if we wanted. OOP allows for doing this easily, we just want to make sure nothing we do will prevent future inheritance.

We might also want custom types rather than the 4 built-in PHP scalars (bool, int, float and string). For example, we would probably want to represent SQL types like DATETIME, DATE and TIMESTAMP with the [Carbon date library](https://carbon.nesbot.com/docs/).

We would also want to represent standard SQL relations in some sort of OO way. Those relationships might include, but should not be limited to:

* Parent Record
    
* Child Records
    
* One To One
    
* Many To Many
    

And how about virtual fields? We might want to show users cost per ounce, but we don't want to have to recompute this every time we change either the price or the number of ounces on a product. We can compute it 100% accurately on demand.

And type checking would also be nice to have. You don't want to assign a random string to an integer. Type checking helps the developer to spot errors quickly.

### Our Requirements

I happen to know all this blue sky thinking is super easy to implement, so let's write out our requirements then we can pause and think about how we are going to implement it.

* Active Record CRUD functionality
    
* Field names match table column names.
    
* Fields typed like table columns
    
* Fields type checked
    
* Field validation
    
* Fields with PHP class types (like Carbon)
    
* Virtual Fields
    
* SQL Relationships
    
* Per class custom logic
    

### Is there an existing package that meets our needs?

One would think it should be fairly easy to find a PHP package that would satisfy these basic requirements, but I was unable to come up with anything reasonable and currently supported. I also have some ideas about database cursors and tables that no one seems to have implemented, so I decided to write my own ORM (Object Relationship Mapper) to do everything I needed. So now I have to think about designing a class (or multiple classes) to do what I need done.

### Time To Think About Implementation

And this is probably the most important part of class design. You need to think about how you are going to implement it. I find the more I specify requirements and then think about it without rushing into coding, the better design I come up with. Obviously, you can still change things as you code and will probably come up with a better implementation as you revise the code, it helps to start with a solid understanding of what you are trying to accomplish and some idea of how to make the vision a reality.

You may also want to think about how things might change in the future. Ideally, your design allows future expansion. But don't make the mistake of implementing something you don't need immediately. Think about how you might implement it in your current design, but don't actually do it. This will help shape your design decisions to be a bit more open and flexible. Often just knowing how you would implement something in the future is enough of a design. Not everything needs to be spelled out in code.

Next time we will dive into the first part of an implementation with a few more requirements and lay the groundwork for extensible classes.

### Takeaways

You have to have an understanding of what you are trying to accomplish before you set out to write code. This is probably the most important aspect of designing anything, knowing where you are going.

It is important to define requirements and nice to haves so you know which direction you want to go towards. It also helps to think about future features even if you don't need them now.

Look for existing packages before you write from scratch. Many people have been solving similar problems for years. See if you can find something that works for you.

And if you need to write code, spend some time just thinking about it in the back of your mind before you start typing.