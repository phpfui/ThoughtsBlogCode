---
title: "Late Static Binding in PHP"
datePublished: Tue Feb 20 2024 00:49:32 GMT+0000 (Coordinated Universal Time)
cuid: clstnedz3000309i84ova4lj2
slug: late-static-binding-in-php
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/1aYp7IFkHRM/upload/08803ea16ca578891d4187103320feae.jpeg
tags: php, sql, orm, activerecord, object-oriented-programming

---

It is time to cover the Active Record class for my ORM. The Active Record design pattern is well known and there are many implementations in PHP to choose from, so why did I decide to write another one? Pretty simple actually. All the current implementations are [bloated, slow](https://github.com/Big-Shark/forked-php-orm-benchmark) and [don't properly exploit](https://laravel.com/docs/10.x/eloquent#generating-model-classes) Late Static Binding!

### The Problem with Eloquent

Eloquent is the ORM for the hugely popular Laravel PHP framework. Unfortunately it is a poorly designed [Active Record class](https://laravel.com/docs/10.x/eloquent). The basic problem with the Eloquent Active Record is the reliance on class member definitions of the record. Each instance of an Eloquent Active Record class carries the entire definition of the record model. This is a glaring error in the design. A basic principal of Object Oriented Design is to factor out common properties to a base class. While Eloquent does that (you don't have to implement the common logic in each class), it forgets that every instance of an Active Record model is the same in terms of properties of record of the table. You don't need to track the same thing in multiple objects. You only need one copy. That sounds like a perfect use case for **static**! By definition, static members of a class are global to all instances of the class. So if you have a model of an SQL table, you know all instances of that class refer to the same table and have the same field properties. This is a text book definition of where you use a static member to represent the properties of a class. So as a result, a collection of Eloquent Active Records contains duplicated data that is the same for each instance. This is clearly wrong and has still not been corrected after all these years!

But how can we have the same static members that define a record be different for all the different records in a database? For example, if we implement an **ActiveRecord** class and then have child classes like **Invoice** and **InvoiceItem**, both of which are **ActiveRecord** classes, how can make the static data different for each class and not impact all instances of classes derived from **ActiveRecord**?

### Enter Late Static Binding

Whoa! **Late Static Binding?** What is that? Sounds complicated! Actually it is pretty simple and has been in PHP since 5.3. In PHP, there are two reserved words that seem to do the same thing, but don't. They are **self** and **static**. You have seen these before:

```php
class Base
    {
	public static string $value = __CLASS__;
	public function asStatic() : string
        {
		return '::static = ' . static::$value . "\n";
		}
	public function asSelf() : string
		{
		return '::self = ' . self::$value . "\n";
		}
	}
class Guardian extends Base
	{
	public static string $value = __CLASS__;
	public function mySelf() : string
		{
		return '::myself = ' . self::$value . "\n";
		}
	public function myParent() : string
		{
		return '::myparent = ' . parent::$value . "\n";
		}
	}
class Child extends Guardian
	{
	public static string $value = __CLASS__;
	public function mySelf() : string
		{
		return '::myself = ' . self::$value . "\n";
		}
	public function myParent() : string
		{
		return '::myparent = ' . parent::$value . "\n";
		}
	}
```

The key words **self** and **static** seem to be the same, but are very different, and when combined with late static binding, produce a very elegant solution for an Active Record class. The **self** keyword indicates to resolve to the value of the member of the current class, while the **static** keyword resolves to the most derived instance of the static member. So in the above example, if we call **asSelf()**, we get the **$value** of the class where **asSelf** is defined. If we call **asStatic()**, then we get the most derived **$value** class and not where **asStatic** was declared in the class **Base**. The **static** keyword returns the value of the most derived child class, while **self** returns the value of the static variable in the current class scope. You can see the **mySelf()** method returns the value of the static member that the current class was initialized to, and not the base or more derived class.

Here is the output to demonstrate how it all works:

```plaintext
Base::static = Base
Base::self = Base
Guardian::static = Guardian
Guardian::self = Base
Guardian::myparent = Base
Guardian::myself = Guardian
Child::static = Child
Child::self = Base
Child::myparent = Guardian
Child::myself = Child
```

And that is the "trick" to **late static binding**. The last derived class that declares a **static** member is the winner of how it should be initialized. So we can declare a static member in our base class of **ActiveRecord**, but it gets initialized by the last and final derived class. We can then have the **ActiveRecord** class access static members knowing that the values are really from the final child class. This way we have a generic static member we can rely on to get the final correct information for the final derived class. And since this information is static, it only appears once in memory, instead of every single instance of the class.

### Leveraging Late Static Binding

In an Active Record ORM, one of the biggest problem is tracking database changes. This is normal handled by a migration system that handles all the database changes needed. But we also have to manage the initialization of the Active Record classes that we need to interact with to use the ORM.

One approach is to determine all this information at run time. While that is possible, it is also slow, as this has to be done EVERY time the PHP script executes. Instead, we could put this information into a class once, then load that class when needed to access a corresponding record in the database. The problem becomes we either have to modify source code, or load the information from some sort of ini file. In looking at the different alternatives, I decided to define the table structure in code. This has the big advantage of absolutely the fastest load time of any of the three approaches, as it is native PHP with no need to access any external resources.

The problem with the modify the code approach when the database changes is you have to deal with generating source code and not overwriting user code. While you can use special delimiters in the source code, this is messy and error prone. A better approach would be to use late static binding and generate a static initialization class! This class would not need to be user modified, since it only contains SQL schema information and no user logic.

Here is how the class hierarchy looks:

* **\\PHPFUI\\ORM\\Record**
    
    * **\\App\\Record\\Definition\\Invoice**
        
        * **\\App\\Record\\Invoice**
            

The **\\PHPFUI\\ORM\\Record** class is the library class that has all the logic of how to implement an active record. The **\\App\\Record\\Definition\\Invoice** class has the static information describing the structure of a record in the table. This class can be automatically generated with PHP code. And finally the **\\App\\Record\\Invoice** has any logic needed that is custom to a specific Invoice. By extending from **\\App\\Record\\Definition\\Invoice**, we get all the information about what fields are in the active record class, and by extending from **\\PHPFUI\\ORM\\Record**, we have full Active Record functionality. And when the database changes, we only have to regenerate **\\App\\Record\\Definition\\Invoice** with some standard PHP, and possibly make changes to **\\App\\Record\\Invoice** if the change was substantial.

### Takeaways

* Late Static Binding "inherits" static members from the most derived child class.
    
* Isolating static initialization from custom code makes it easier to update.
    
* Separate out members that are common to all instances of the class into statics.
    
* Classes should only contain member properties that are unique to that object and not the class in general.
    

**PREVIOUS:** - [Implementing Active Tables in OO PHP](https://blog.phpfui.com/implementing-active-tables-in-oo-php)