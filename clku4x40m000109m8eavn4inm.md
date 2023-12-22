---
title: "PHP Database Cursors"
datePublished: Wed Aug 02 2023 19:42:21 GMT+0000 (Coordinated Universal Time)
cuid: clku4x40m000109m8eavn4inm
slug: php-database-cursors
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/GSiEeoHcNTQ/upload/00ff569085cdd97ce9e03d7b23bd8c00.jpeg
tags: oop, php, sql, orm

---

In a [previous post](https://blog.phpfui.com/iterators-and-database-cursors-in-php), I talked about the PHP Iterator interface and how to write a CSV reader. Now it is time to do the same thing for a database cursor. Most of this code will look familiar, as it has to do the same thing, but will use a different data source, a SQL SELECT query.

### Three types of Database Cursors

While I want to implement a database cursor on an Active Record class, often database queries will return more information (think JOIN) than an Active Record class (which only models one record from a table) can hold. Since PHP highly leverages associative arrays, it makes sense to model a database cursor returning an associative array, as our CSV reader does. But arrays are kind of limited in what they can do. A more flexible approach would be to return an object, but not a fully typed checked Active Record. This will make the code a bit more generic and extensible. We can also implement ArrayAccess on the DataObject that the cursor will return, so now we have something that acts like an array, and has more functionality than a basic array but is not an array.

So our three types of database cursors will be **ArrayCursor**, **DataObjectCursor** and **RecordCursor**.

### Hoisting Out Common Code

One of the design principles of OO design is to hoist common code out of a class and put it in the parent class, where it can be used by multiple children of the parent class but can be tweaked in the child class. So this calls for an abstract parent class, one that can be inherited from, but not instantiated as an object directly.

Let's start coding!

```php
abstract class BaseCursor implements \Countable, \Iterator
```

The base cursor class needs to implement Iterator, but also Countable, since we want the cursor to mimic an array, which is countable.

### Next Step: Common Data

```php
protected ?int $index = null;
private ?int $count = null;
private ?\PDOStatement $countStatement = null;
private ?int $total = null;
private ?\PDOStatement $totalStatement = null;
```

Our cursors have an index, which will be the zero-based record number, so we can use the $index property for that.

We also need to count the number of records in the query, so we will cache that number in the $count property. Notice that count is the number of records returned by the query affected by the limit clause. This is the expected number of records the specific query will return, which could be less than the limit clause if there are fewer records matching the search criteria than the limit, or the last query in a paginated query which is almost always less than the limit clause.

Another property that would be nice to have is the total number of records that matched the query. This is the $total property.

We also have some internal housekeeping to keep track of the queries for the count and total records. These are PDOStatements we will need to get from the Active Table class that knows this information.

Notice the only property where that is not private is $index. This is because it is useful to child classes, while the count and total related properties are private, as the child classes will have nothing to add, as all this information is generic and does not change for the child classes.

### Construct and Destruct

```php
public function __construct(protected ?\PDOStatement $statement = null, private readonly array $input = [])
  {
  }

public function __destruct()
  {
  $this->statement?->closeCursor();
  $this->index = -1;
  $this->total = null;
  $this->count = null;
  }
```

In PHP 8.0, we can promote constructor parameters to properties, so now we have the basic PDOStatement which represents the query and the data associated with the query. $statement is needed by child classes, so it is protected, but $input is only used by the base class, so it is private. This prevents child classes from meddling in our business!

We also implement a destructor. In PHP, people tend to not implement destructors simply because PHP throws everything away at the end of the execution of a server request. But in our case, we use the destructor to manage a resource, which is the PDO cursor that will see initialized later in the code. Managing resources is the primary reason for destructors. If your class is not managing a resource (open file, open connection, or anything with an open / close functional interface returning a handle), then you probably ignore destructors. But to avoid SQL connection leaking, we need the destructor to automatically release the open handle.

We also reset some internal states, but this is more belt and suspender code. It is a flag to the next developer that this object is now done.

### The Easy Stuff

Since we are an abstract class, we need to define the entire interface, but we may not want to implement a default implementation. Let's get the easy stuff out of the way:

```php
abstract public function current() : mixed;
abstract public function next() : void;
abstract public function valid() : bool;
```

The **current**(), **next**() and **valid**() methods all relate to the type of object we are iterating over, so there is no generic implementation we can use. An array is created differently than an ActiveRecord, which is probably different from a DataObject. They have different ways to validate and return the current record. So we declare them as abstract and leave the implementation of all these to the child class to do what is best for them.

### Common Generic Code

There are a couple of things that are generic to database cursors, and those we can add to the base abstract class:

```php
protected function init() : void
  {
  if (null === $this->index)
    {
    $this->rewind();
    }
  }

public function rewind() : void
  {
  if (! $this->statement)
    {
    return;
    }
  $this->index = -1;
  $this->statement->closeCursor();
  $result = \PHPFUI\ORM::executeStatement($this->statement, $this->input);
  if ($result)
    {
    $this->next();
    }
  }

public function key() : int
  {
  $this->init();
  return (int)$this->index;
  }
```

The first thing we see here is the **init**() method. Since we constructed our object without doing anything other than setting some values, we have not done a query to the database. We may never do a query to the database depending on the logic of the system using our class, but we want to make sure we are initialized correctly if our user decides to get something from the cursor. I use an init function to wrap all this and delay initialization if possible. **init**() simply checks for the initial value of $index and calls the rewind method, which does the actual work of executing the query. We will call init anytime we need to make sure the data has been initialized. It is a low overhead call, as it simply checks the value of $index and returns if initialized.

The **rewind**() method is where all the work happens. First, we see if we have a valid statement. If we don't have a valid $statement property, we don't crash, we simply don't iterate. Then we set the $index property to -1, as are going to start (or start over) reading data, and the first record is zero-based, and **next**() will increment the index later.

We then close the current cursor, since we are going to make a new one. If no cursor has been opened, this call does nothing, which is fine with us.

Next, we call **exectuteStatement**() to run the statement and log any errors for us. If no errors, we call **next**() to do the actual work of getting the first item from the initial query.

And finally, the **key**() method just makes sure we are initialized and returns the current index.

### The Hard Stuff

Finally, we get to the harder part of the class, keeping track of the count of records the cursor is iterating over, and the total number of records of an unlimited query.

We will do the total logic first, as it is the easier of the two:

```php
public function setTotalCountSQL(string $totalSql) : static
  {
  $this->totalStatement = \PHPFUI\ORM::pdo()->prepare($totalSql);
  return $this;
  }

public function total() : int
  {
  if (null === $this->total && $this->totalStatement)
    {
    if ($this->totalStatement->execute($this->input))
      {
      $this->total = (int)$this->totalStatement->fetch(\PDO::FETCH_NUM)[0];
      }
    }
  if (null === $this->total)
    {
    $this->total = $this->count();
    }
  return $this->total;
  }
```

First, we need to allow the ORM to set the query for the cursor. The cursor does not know about the query, it just iterates over a result set. So we can get the total record count from the ORM, as the ORM knows how to do this, whereas the cursor does not. We prepare it and save it for later.

When **total**() is called, we check to see if we have not executed the total query yet. This delays execution in case our user does not care about the total number of records the query returned. We then execute the query and cache the result in case our user requests it again. If we still don't have a valid total, we default to the count.

And finally, for the count, we do something similar, except we can set the query count directly if needed. This can be used to optimize a query if we have computed the count for another reason.

```php
public function count() : int
  {
  if (null === $this->count && $this->countStatement)
    {
    if ($this->countStatement->execute($this->input))
      {
      $this->count = (int)$this->countStatement->fetch(\PDO::FETCH_NUM)[0];
      }
    }
  if (null === $this->count)
    {
    $this->init();
    $this->count = $this->statement ? $this->statement->rowCount() : 0;
    }
  return $this->count;
  }

public function setCountSQL(string $limitedSql) : static
  {
  $this->countStatement = \PHPFUI\ORM::pdo()->prepare($limitedSql);
  return $this;
  }

public function setQueryCount(int $count) : self
  {
  $this->count = $count;
  return $this;
  }
```

## Takeaways

* Host common code to the parent class.
    
* Use abstract classes to share common code.
    
* Declare abstract methods for anything you need the child class to implement.
    
* Make properties protected if they are useful to child classes.
    
* Properties that are controlled and depended upon by the current class should be private.
    
* The fully commented and prettified code can be found [here](https://github.com/phpfui/ORM/blob/main/src/PHPFUI/ORM/BaseCursor.php).
    

**PREVIOUS:** - [Active Table Design Pattern](https://blog.phpfui.com/active-table-design-pattern)