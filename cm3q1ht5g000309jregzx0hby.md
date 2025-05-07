---
title: "Implementing Active Records in PHP - Part 2"
datePublished: Wed Nov 20 2024 15:29:48 GMT+0000 (Coordinated Universal Time)
cuid: cm3q1ht5g000309jregzx0hby
slug: implementing-active-records-in-php-part-2
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/qyzWgOSa_WU/upload/0f4f0d123445484cb2f8224180ec9a43.jpeg
tags: oop, design-patterns, php, orm, oops

---

In our last episode, I wrote a class I call DataObject. It will be the base class for my Active Record class since it has the basics of what is required in an Active Record class.

A DataObject is basically a OO wrapper around an array, but an Active Record needs to do more, like get, set and validate fields, implement relations, and of course do the basic CRUD functions. Obviously we are going to implement `__get` and`__set` magic functions, but how do we know what to get and set?

The answer is Late Static Binding and our intermediate class containing all the field information from the SQL table. I keep track of the field name (the index into the array that describes the table), and then the properties of that field, including its type, nullable and default values. So if a field is not an index in this table, it is not a valid field. I can also report on type and null errors and provide a valid default record.

### Implementing Virtual Fields

Another feature of my Active Record class is support for virtual fields. You can make any virtual field you want with any relation you want. And it only takes a few lines of code. Here is the `__get()` function:

```php
public function __get(string $field) : mixed
	{
	$relationship = static::$virtualFields[$field] ?? false;
	if (\is_array($relationship))
		{
		$relationshipClass = \array_shift($relationship);
		$relationshipObject = new $relationshipClass($this, $field);
		return $relationshipObject->getValue($relationship);
		}
	return parent::__get($field);
	}
```

A specific Record class can set up a virtual field by adding an array indexed by the virtual field name. We check to see if the field being accessed is a virtual field, and if it is an array. Then we take the first element in the array and create an object from it. We pass the current active Record ($this) and the field name it was called with.

We then call `getValue()` and pass it the rest of the array. Any class that derives from `\PHPFUI\ORM\VirtualField` be used. All you need to do is figure out what needs to be returned by `getValue()`. And `setValue()` works exactly the same way, but called by `__set()` instead.

And finally, we just call the parent (`\PHPFUI\ORM\DataObject`) `__GET()` and we are done implementing virtual fields! Another simple example of using inheritance with a ISA relationship.

### What About Setting an Active Record Property?

Setting a variable is a bit more complicated, mostly because we need to add type checking and setting related fields in an OO manor.

First, we should implement setting virtual fields. Also simple. Just another 5 lines, so we took all of 10 lines of code (plus a few for the base VirtualField class) to implement virtual fields! Here is `__set()`:

```php
public function __set(string $field, mixed $value) : void
	{
	$relationship = static::$virtualFields[$field] ?? false;
	if (\is_array($relationship))
		{
		$relationshipClass = \array_shift($relationship);
    	$relationshipObject = new $relationshipClass($this, $field);
		$relationshipObject->setValue($value, $relationship);
		return;
		}
```

Notice I don’t actually set the record for the virtual field. This is because as a generic Record object, I have no idea how to do that for a random virtual field. If the virtual field can set something, it must do it directly with the record it was passed.

### Setting Related Records

While we can get related records by just looking at the field name, we need to do some more work to set a related record. Ideally we want to do this:

```php
$order->employee = $salesEmployee;
$order->company = $purchasingCompany;
$order->update();
```

We can figure out how to save the relation by getting the primary key of our record and assigning that to our current record.

```php
	$id = $field . \PHPFUI\ORM::$idSuffix;
	if (isset(static::$fields[$id]) && $value instanceof \PHPFUI\ORM\Record)
		{
		$haveType = $value->getTableName();
		if ($field == $haveType)
			{
			if ($value->empty())
				{
				$this->current[$id] = 0;
				return;
				}
			$this->empty = false;
			if (empty($value->{$id}))
				{
				$this->current[$id] = $value->insert();
				}
			else
				{
				$this->current[$id] = $value->{$id};
				}
			return;
			}
		$haveType = \PHPFUI\ORM::getBaseClassName($haveType);
		$recordNamespace = \PHPFUI\ORM::$recordNamespace;
		$message = static::class . "::{$field} is of type \\{$recordNamespace}\\" . \PHPFUI\ORM::getBaseClassName($field) . " but being assigned a type of \\{$recordNamespace}\\{$haveType}}";
		\PHPFUI\ORM::log(\Psr\Log\LogLevel::ERROR, $message);
        throw new \PHPFUI\ORM\Exception($message);
		}
```

We check to see if the field being set has a corresponding id as a suffix and that we are assigning an active record of the same name as our field. If the record is empty, then unset the value and return. If the record has not been saved yet, we save it now to get a primary key. Finally we assign the primary key to our current record.

If these conditions are not met, we throw a type error.

### Normal Assignment

Now that we have implemented virtual fields and related records, what is left is just a normal assignment. We check if nulls are allowed and make sure the type is correct, and if not, cast it correctly. Then do the actual assignment and set the empty flag to false, since we know we set something in the record. Here is the code:

```php
	$this->validateFieldExists($field);
	$expectedType = static::$fields[$field][self::PHP_TYPE_INDEX];
	$haveType = \get_debug_type($value);
	if (null === $value)
		{
		if (! static::$fields[$field][self::ALLOWS_NULL_INDEX])
			{
			$message = static::class . "::{$field} does not allow nulls";
			\PHPFUI\ORM::log(\Psr\Log\LogLevel::WARNING, $message);
			throw new \PHPFUI\ORM\Exception($message);
			}
		}
	elseif ($haveType != $expectedType)
		{
		$message = static::class . "::{$field} is of type {$expectedType} but being assigned a type of {$haveType}";
		\PHPFUI\ORM::log(\Psr\Log\LogLevel::WARNING, $message);
		// do the conversion
		switch ($expectedType)
			{
			case 'string':
				$value = (string)$value;
				break;
			case 'int':
				$value = (int)$value;
				break;
			case 'float':
				$value = (float)$value;
				break;
			case 'bool':
				$value = (bool)$value;
				break;
			}
		}
	$this->empty = false;
	$this->current[$field] = $value;
	}
```

And that is the core of an Active Record class. Obviously there are plenty of helper routines like `__isset()` that allows `empty($object)` to work correctly. And we need to implement the CRUD functions, but those are mostly creating SQL statements from our current record.

You can find the [full source here](https://github.com/phpfui/ORM/blob/main/src/PHPFUI/ORM/Record.php).

### Takeaways

Always host code out of the current class into a base class when there is nothing specific to the current class. This was the case for `__set()` and all we had to do was add virtual field support, then call the base class method.

Make child classes deal with any specifics. Virtual fields just defined an interface for the class. How that works is up to the virtual field implementation. We can just write to the interface.

Next time we can get into validation, which is a key component of active records.

**PREVIOUS:** - [Implementing Active Records in PHP - Part 1](https://blog.phpfui.com/implementing-active-records-in-php-part-1)