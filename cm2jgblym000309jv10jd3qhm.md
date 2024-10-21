---
title: "Implementing Active Records in PHP - Part 1"
datePublished: Mon Oct 21 2024 20:10:47 GMT+0000 (Coordinated Universal Time)
cuid: cm2jgblym000309jv10jd3qhm
slug: implementing-active-records-in-php-part-1
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/t4ScXt5nVOc/upload/6cbc3869a5f602f7081bd847ab4be203.jpeg
tags: oop, php, orm, oops, orm-object-relational-mapping

---

Active Records are a defined [Design Pattern](https://en.wikipedia.org/wiki/Software_design_pattern) with it’s own [Wikipedia page](https://en.wikipedia.org/wiki/Active_record_pattern)! So why create another Active Record implementation? Pretty simple. The existing PHP Active Record implementations are bloated and slow and hard to configure. So here is how to implement something lean and mean!

### Lets Get to Work!

An Active Record class has to do some basic things, like validate that a field exists and allow set and get for valid members. This sounds like a base class to me. Let’s see how we might hoist some functionality into a useful object.

First, the constructor. We would want to create an object from an array, as arrays are returned natively from the PDO interface.

```php
	public function __construct(protected array $current) {}
```

We have now defined how to create an object with a property of $current. And since we have constructed from an array, it would be nice to support array syntax on our new object.

### Enter ArrayAccess

**ArrayAccess** is a standard PHP interface. And to implement it, we just need to implement the following methods:

public [offsetExists](https://www.php.net/manual/en/arrayaccess.offsetexists.php)([mixe](https://www.php.net/manual/en/language.types.mixed.php)[d $off](https://www.php.net/manual/en/arrayaccess.offsetexists.php)[set):](https://www.php.net/manual/en/language.types.mixed.php) [bool](https://www.php.net/manual/en/language.types.boolean.php)

pub[lic](https://www.php.net/manual/en/language.types.boolean.php) [offsetGet](https://www.php.net/manual/en/arrayaccess.offsetget.php)([mix](https://www.php.net/manual/en/language.types.mixed.php)[ed $](https://www.php.net/manual/en/arrayaccess.offsetget.php)[offset](https://www.php.net/manual/en/arrayaccess.offsetexists.php)[): mi](https://www.php.net/manual/en/language.types.mixed.php)[x](https://www.php.net/manual/en/arrayaccess.offsetexists.php)[ed](https://www.php.net/manual/en/language.types.mixed.php)

publi[c of](https://www.php.net/manual/en/language.types.boolean.php)[fsetSet](https://www.php.net/manual/en/arrayaccess.offsetset.php)([mi](https://www.php.net/manual/en/language.types.mixed.php)[xed](https://www.php.net/manual/en/arrayaccess.offsetset.php) [$](https://www.php.net/manual/en/arrayaccess.offsetget.php)[offset](https://www.php.net/manual/en/arrayaccess.offsetexists.php)[, mix](https://www.php.net/manual/en/language.types.mixed.php)[e](https://www.php.net/manual/en/arrayaccess.offsetexists.php)[d](https://www.php.net/manual/en/language.types.mixed.php) $value)[: vo](https://www.php.net/manual/en/language.types.boolean.php)[id](https://www.php.net/manual/en/language.types.void.php)

p[ubli](https://www.php.net/manual/en/language.types.void.php)c [o](https://www.php.net/manual/en/arrayaccess.offsetunset.php)[f](https://www.php.net/manual/en/arrayaccess.offsetset.php)[fsetUnset](https://www.php.net/manual/en/arrayaccess.offsetget.php)([m](https://www.php.net/manual/en/language.types.mixed.php)[ixed](https://www.php.net/manual/en/arrayaccess.offsetunset.php) [$offset](https://www.php.net/manual/en/arrayaccess.offsetexists.php)[):](https://www.php.net/manual/en/language.types.mixed.php) [v](https://www.php.net/manual/en/arrayaccess.offsetexists.php)[oid](https://www.php.net/manual/en/language.types.void.php)

Like this:

```php
	public function offsetExists($offset) : bool
		{
		return \array_key_exists($offset, $this->current);
		}

	public function offsetGet($offset) : mixed
		{
		if (\array_key_exists($offset, $this->current))
			{
			return $this->current[$offset];
			}
		throw new \PHPFUI\ORM\Exception(self::class . " {$offset} is not defined");
		}

  public function offsetSet($offset, $value) : void
		{
		if (! \array_key_exists($offset, $this->current))
			{
			throw new \PHPFUI\ORM\Exception(self::class . " {$offset} is not defined");
			}
		$this->current[$offset] = $value;
		}

	public function offsetUnset($offset) : void
		{
		unset($this->current[$offset]);
		}
```

We might also want to get an array out of our object:

```php
	public function toArray() : array
		{
		return $this->current;
		}
```

We might also want to add a few more methods to match standard PHP functions such as empty() and isset().

```php
	public function empty() : bool
		{
		return ! \count($this->current);
		}

	public function isset(string $field) : bool
		{
		return \array_key_exists($field, $this->current);
		}
```

And of course, we want to set fields in our object:

```php
	public function __set(string $field, mixed $value) : void
		{
		if (! \array_key_exists($field, $this->current))
			{
			throw new \PHPFUI\ORM\Exception(self::class . " {$field} is not defined");
			}
		$this->current[$field] = $value;
		}
```

Notice for get and set type functions, we throw an exception. Why do we do this? Simply to enforce type safety in our class and program. If we allow arbitrarily setting of any value, then we don’t have control of our destiny. We are allowing anything, like a normal array. But we want some order to our madness. So we insist that if we set a value in our object, it has to exist. The same thing applies to getting a value out of our object. If the value does not exist, that is a problem, and we throw and exception.

But since we are doing an Active Record model, we also want to support relations in the database.

### What is a relation?

A related record is just that. This record is related to another record. Just like you are related to your mother and father (your parents), you are also related to your siblings (sisters and brothers) and your children. There is a direct connection between you and your parents, brothers, sisters and children. In a database, these relations are defined by the schema, rather than some genetic DNA. At the basic level, a related record could be a parent, sibling, or child. The database schema determines what is what. But at a basic level, an ID that refers to another table is a related record. It could be a parent, sibling, or even child (but probably not, as you can have multiple children, but there is only one ID), but this depends on your database structure.

In my ORM, I decided to automatically relate any field that ends in ‘Id’. So the **memberId** field would be the related record to the current record with a type of **Member** in the **member** table.

### Implemented Related Records

The work is done in the \_\_get magic method:

```php
	public function __get(string $field) : mixed
		{
		if (\array_key_exists($field, $this->current))
			{
			return $this->current[$field];
			}
		// could be a related record, see if has a matching Id
		if (\array_key_exists($field . \PHPFUI\ORM::$idSuffix, $this->current))
			{
			$type = '\\' . \PHPFUI\ORM::$recordNamespace . '\\' . \PHPFUI\ORM::getBaseClassName($field);
			if (\class_exists($type))
				{
				return new $type($this->current[$field . \PHPFUI\ORM::$idSuffix]);
				}
			}
		throw new \PHPFUI\ORM\Exception(self::class . " {$field} is not a valid field");
		}
```

First we see if the key exists. If so, we are done and we just return it.

But because it does not exist, it might be a related record if it ends with our idSuffix (which I make user configurable for added flexibility). We simply append the id, then check if that field exists. If it does, then we probably have a related record. First we normalize the name to our record namespace (also user configurable), then see if the class exists. If it does, then it is related record!

Then we can just new the class with the value of our id, since our Record class will construct from an int as a primary key, and then return it. That is it, we are done! Of course if the member field or class does not exist, we throw an error.

### So What Did We Just Build?

We construct from an array, and now we have an object that validates on getting and setting members, works like an array, and also implements related records. It turns out we need all this functionality for our Active Record class. But that is for next time.

### Takeaways

* Always validate access. Normal PHP classes now do this by default, but we should also implement it in any code we control.
    
* Always throw exceptions for programmer errors. Accessing an invalid field is a programming error (not a user error), and the developer should be immediately informed.
    
* Add useful functionality early in the class hierarchy, as it will simplify child classes.
    
* Implement useful interfaces such as ArrayAccess if it makes sense.
    

**PREVIOUS: -** [**Late Static Binding in PHP**](https://blog.phpfui.com/late-static-binding-in-php)