---
title: "Implementing Active Tables in OO PHP"
datePublished: Tue Feb 06 2024 23:37:41 GMT+0000 (Coordinated Universal Time)
cuid: clsb03wts000209l6blh95lq3
slug: implementing-active-tables-in-oo-php
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/ULh0i2txBCY/upload/f7d8cdea396ccae61a11a68431362a5d.jpeg
tags: oop, php, sql, orm, oops, object-oriented-programming

---

You have probably heard of the **Active Record** design pattern, but now I am going to introduce a new design patterned called an **Active Table**. Just like the **Active Record** design pattern, which allows use to use a higher level of abstraction than constructing raw SQL to get and save individual records in a database, we can do the same thing for a SQL table.

### The Base SQL Functions

There are four basic things you can do with a table, **SELECT**, **UPDATE**, **INSERT** or **DELETE** records. So let's start writing code:

```php
public function update(array $variables) : static;
public function delete(bool $allowAll = false) : static;
public function insert(array $records, string $ignore = '') : static;
```

This is all pretty simple. The update statement is simply passed an associative array with the field name and value. The delete statement only has a fail safe "don't delete the entire table by mistake" flag. And insert just inserts an array of records and can ignore duplicates if needed.

Notice I did not include the **SELECT** method. Why? Because we need more flexibility than a single return type to select things from the data. I previously described **ArrayCursor**s, **DataObjectCursor**s and **RecordCursor**. So while update, delete and insert don't return things to iterate over, select statements do, so we need to treat them a bit differently. All these methods are effectively SELECT statements:

```php
public function getArrayCursor() : \PHPFUI\ORM\ArrayCursor;
public function getDataObjectCursor() : \PHPFUI\ORM\DataObjectCursor;
public function getRecordCursor() : \PHPFUI\ORM\RecordCursor;
public function getRows() : array;
```

### Select What?

What is a Select statement, but simply a list of fields you want to select. By default we can be lazy and use \*. But if we wanted to get more precise, it is easy to add things to select:

```php
public function addSelect(string | object $field, string $as = '') : static;
```

If the $field is string, we can simply escape it. If it is an object, we can take the string representative of the object and use that (more on this later). And the $as parameter is rather obvious. We can call addSelect() multiple times to add multiple fields. All fairly simple.

### Where Art Thou

With the exception of insert, we probably need to know more about what to update, delete or select. In SQL we can control that with the where clause.

```php
public function setWhere(?\PHPFUI\ORM\Condition $condition = null) : static;
public function getWhereCondition() : \PHPFUI\ORM\Condition;
```

Notice I am using my previously constructed **Condition** class for where, because that is what the where clause is, a condition! I can also get the currently set condition and then add to or modify it if I wanted. Now your are starting to see the power of an OO **Active Table** design, where I can modify what was previously set without having to deal with constructing SQL statements by hand in text. And remember, conditions can be as complex as you want. They fully nest! We can repeat the exact same logic for the **HAVING** condition. The power of OOP!

### Do the Easy Stuff First!

An aside here: I always do the easy stuff first. If I planned on something taking a week and involving 10 pieces that need to done, I will knock off the easy stuff first. Why? Because when you have to report progress, it is always easier to say you have completed 70% of the project in 2 days, rather than doing the hardest part first and having it take 2 days, and only report you are 10% done.

### Easy Peasy (Limits and Pagination)

Another thing we might want to do is limit the query:

```php
public function setLimit(int $limit = 20, ?int $page = null) : static;
public function setOffset(int $offset) : static;
```

Notice here we are providing a higher level paging service than SQL provides by default. Since we can specify a page, we can think in terms of a paginated dataset, rather than having to compute the offset by ourselves, which of course we can still do.

### Easy Peasy Part II (Order By)

Ordering is another easy thing to add to our **Active Table**:

```php
public function addOrderBy(string $field, string $ascending = 'ASC') : static;
public function setOrderBy(string $field, string $ascending = 'ASC') : static;
```

Notice we have **addOrderBy** in addition to **setOrderBy**. **setOrderBy** will overwrite the existing ordering, where as **addOrderBy** will just add another order clause to the existing ordering clauses. **addGroupBy** and **setGroupBy** work exactly the same way.

### Easy Peasy Part III (Group By)

Can't get much simpler than this:

```php
public function addGroupBy(string $field, bool $rollup = false) : static;
public function setGroupBy(string $field, bool $rollup = false) : static;
```

Note the naming pattern here. Add vs Set. Add methods add to the existing values, while Set methods clear the existing values and start fresh with the new value.

### Unions Jack

Unions are data sources from another query with the same column definitions. Since we already have a way to express a query (the **Table** class itself), we can easily add support for unions.

```php
	/**
	 * Add table for union.
	 *
	 * @param bool $any if true, adds all records from query, defaults to distinct records only
	 */
	public function addUnion(\PHPFUI\ORM\Table $table, bool $any = false) : static;
```

### Joins, finally!

Of course the elephant in the room is how do you implement joins? Joins are probably the most common and complex thing in SQL statements. Joins have four basic properties. The table we are joining, the condition we are join on, the type of join, and finally the AS clause to make the join unique if needed.

```php
	/**
	 * Add a join with another table
	 *
	 * @param string $table name of the table to join, case sensitive
	 * @param string | \PHPFUI\ORM\Condition $on condition.  If string, name of field on the $table.  Defaults to table name appended with Id. Or \PHPFUI\ORM\Condition for complex joins
	 * @param string $type of join
	 */
	public function addJoin(string $table, string | \PHPFUI\ORM\Condition $on = '', string $type = 'LEFT', string $as = '') : static;
```

Obviously we need the table name to join, nuff said. But what to join on? A Join has a condition, and lucky for us, we have a class for that. But what if we want to simplify common joins? Often a join is on the same field name in both tables. Even more often is the primary key of the current table is the field you want to join with the other table. This works for both child and related records. For example, if we had a membership table with one or more members per membership, we could simply just join on the member table and we could automatically assume we are joining on membershipId. Or we could pass the common field name as a string. But if we wanted to get fancy, we could simply make a **Condition** and use that. After all, we did build a **Condition** class for situations just like this. Again, the power of OO strikes again.

Finally we have the type of join (LEFT, RIGHT, INNER, OUTER, etc) and an AS alias if we need it.

### Odds and Ends

One of the things we need to do with SQL statements is have access to functions and other things that might be needed in a condition or select. But how can we distinguish between an SQL field that should be escaped and a function call that should not be escaped. The answer is simple. We default strings to the most common case, which is column names and escape them, then use a **Literal** object to wrap things that are not to be escaped. This puts the burden on the developer to do the right thing, but by default, we escape fields, which will be safer, and assume the developer will do the right think when they use the **Literal** class.

```php
class Literal implements \Stringable
	{
	public function __construct(private readonly string $name) {}
	public function __toString() : string {	return $this->name;	}
	}
```

As you can see, the **Literal** class simply does not do anything to the string, yet allows us to pass a string directly into the SQL output.

Another thing we might want to do is have an explicit **Field** class. Instead of a simple literal, we can use the **Field** class to correctly escape a field.

```php
class Field implements \Stringable
	{
	private string $fieldName = '';
	public function __construct(string $name, string $as = '')
		{
		$parts = \explode('.', $name);
		$dot = '';
		foreach ($parts as $part)
			{
			$this->fieldName .= $dot . '`' . $part . '`';
			$dot = '.';
			}
		if ($as)
			{
			$this->fieldName .= ' AS `' . $as . '`';
			}
		}
	public function __toString() : string
		{
		return $this->fieldName;
		}
	}
```

### Implementation

I have just been covering the interface for an Active Table class, but you can find the [full implementation here](https://github.com/phpfui/ORM/blob/main/src/PHPFUI/ORM/Table.php).

### Takeaways

* Objects are useful for adding unique behavior. We saw this with the **Literal** and **Field** classes.
    
* Look for parts of your problem that are the same in multiple places. This is a class example of where a class will help you encapsulate a concept, witness the **Condition** class. We used it for the WHERE, HAVING and JOIN clauses.
    
* Match the method name to the action you want to perform on the object. See update(), delete() and insert().
    
* Use Add and Set method prefixes to suggest how to use the class.
    
* Do the easy stuff first. Often you can continue to think about the more complex parts of the problem in the background as you pound out the simple stuff.
    

**PREVIOUS:** - [Modeling SQL Conditions in OO PHP](https://blog.phpfui.com/modeling-sql-conditions-in-oo-php)