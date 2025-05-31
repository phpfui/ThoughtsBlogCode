---
title: "Modeling SQL Conditions in OO PHP"
datePublished: Fri Jan 12 2024 22:22:21 GMT+0000 (Coordinated Universal Time)
cuid: clrb7eqed000209jxcksu0avc
slug: modeling-sql-conditions-in-oo-php
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/Y9kOsyoWyaU/upload/8323e5f426ed955178641fc375b1cbab.jpeg
tags: oop, php, sql, orm, oops, object-oriented-programming

---

One of the biggest problems in abstracting SQL into an Object Oriented design is how to model the WHERE, HAVING and JOIN condition expressions. You know the standard =, &lt;&gt;, &gt;, &lt;, &lt;=, &gt;=, etc. operators that are the bread and butter of a condition.

### Operators

SQL has the standard operators we all know and love, basically less than, greater than, equal and not equal. But also AND, OR, NOT, LIKE and IN to name some more common operators.

A common solution in PHP ORMs tends to look something like this:

```php
$select->where('id', 123);
```

Or even

```php
$select->where('status', '=', 'active');
```

Since = is probably the most common use case for an operator, I decided to make it the default. No sense in having to always specify the equal sign when 90% of the use cases will use an equal expression.

I also wanted some type checking to avoid typos and facilitate static analyzers like PHPStan. A string literal is impossible to type check statically and it is always best to detect typos as soon as possible. So I came up with the following list of operators and added them to the Operator namespace: **Equal, GreaterThan, GreaterThanEqual, In, IsNotNull, IsNull, LessThan, LessThanEqual, Like, NotEqual, NotIn, NotLike**

If you combine the above with the logical operators: **AND, OR, AND NOT, OR NOT,** you start to get close to OO expressions. But how to express parentheses you inevitably need to use to make your logic work?

### Parentheses

In deciding to write my own ORM, I needed to represent conditional expressions that would allow me to easily create understandable conditions for the **WHERE, HAVING** and **JOIN** clauses. The biggest issue was how to model the nesting of conditions. If all of your conditions are simple **AND** clauses, life is easy, but as soon as you get to an **OR** condition, things get complicated fast. For example, take the following condition: purchasedDate='2024-02-01' AND lastName LIKE '%robert%' OR firstName LIKE '%robert%' We clearly don't want anyone with a firstName of Robert, we want either firstName or lastName to contain Robert and have a purchasedDate of 2023-02-01. So we would put parenthesis around the like clauses like such: purchasedDate='2023-02-01' AND (lastName LIKE '%robert%' OR firstName LIKE '%robert%').

So how do we represent parenthesis in an expression? The answer turns out to be a **Condition** is always parenthesized. Adding a **Condition** object to another **Condition** object will add parenthesis around the added object. Also, extra parenthesis around a single condition will never hurt anything, as SQL knows how to deal with parenthesis even when they are not required.

So a **Condition** can have any number other **Conditions** added to it, each glued together with one of the logical operators, AND, OR, AND NOT, and OR NOT.

So here is how we would implement our example expression:

```php
$searchName = '%robert%';
$condition = new \PHPFUI\ORM\Condition('purchaseDate', '2024-02-01');
$nameCondition = new \PHPFUI\ORM\Condition('lastName', $searchName, new \PHPFUI\ORM\Operator\Like());
$nameCondition->or('firstName', $searchName, new \PHPFUI\ORM\Operator\Like());
$condition->and($nameCondition);
```

This evaluates to the following (with substitutions made):

```plaintext
`purchaseDate` = '2024-02-01' AND (`lastName` LIKE '%robert%' OR `firstName` LIKE '%robert%')
```

### Outputting Our Expression

Since we are writing code that needs to escape user input, we actually don't want to just return a text string with the fields inserted in plain text as above. The PDO object does this for us, so let's leverage that functionality and get two things, the string representation of the expression with placeholders instead of string values, and also the values we need.

Obviously, **\_\_toString** would be a good match for returning the plain SQL. Then we can return a matching array of values used in the expression with **getInput()**

[You can find the code and documentation here](http://phpfui.com/?n=PHPFUI%5CORM&c=Condition).

### Conclusion

Objects have provided great value for us in this situation. First, since we can easily nest objects, we have an elegant solution to the parenthesis problem. We can also save the state of the expression and pull out the text representation and substituted values for use later in the program. We have also enabled some basic static and dynamic type checking.

Another win for OO design!

**NEXT:** - [Implementing Active Tables in OO PHP](https://blog.phpfui.com/implementing-active-tables-in-oo-php)

**PREVIOUS**: - [PHP Database Cursors](https://blog.phpfui.com/php-database-cursors)