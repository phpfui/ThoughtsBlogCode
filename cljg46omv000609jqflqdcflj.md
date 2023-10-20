---
title: "Active Table Design Pattern"
datePublished: Wed Jun 28 2023 19:33:19 GMT+0000 (Coordinated Universal Time)
cuid: cljg46omv000609jqflqdcflj
slug: active-table-design-pattern
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/z40FFxn34SY/upload/f9a096c19fd07ee2ff8a6c6dcb60c462.jpeg
tags: oop, php, sql, orm, activerecord

---

In [my last post](https://blog.phpfui.com/iterators-and-database-cursors-in-php), I discussed Iterators and Database Cursors as requirements for what I would want in an ORM. I previously decided that I want an [Active Record](https://blog.phpfui.com/zen-and-the-art-of-class-design) for my ORM. But there is another requirement for any decent ORM, and that is querying the database. While I can have Active Records and Database Cursors, I need to define a way to get the cursor or an active record out of the database. So more design work is needed before we get into code.

### More Requirements

One of the things you need to do with databases is query and update them, or why else have a database? The Active Record is a well-known design pattern and even has its own [wikipedia page](https://en.wikipedia.org/wiki/Active_record_pattern). The Active Record pattern only deals with one record. A Database Cursor can iterate over an effective collection of active records, but how can we get the original collection to feed to the database cursor? In SQL databases, what we are looking for is a SELECT statement that returns all records matching the criteria (where clauses, limits, orders, etc). Instead of selecting those records, we might want to update them in mass, or even delete them.

So we need a way to manipulate a SQL table and perform selects, updates, deletes and inserts, the four basic things you can do with a database.

### Enter Active Table Design Pattern

Just like we can manipulate an Active Record (change field values, save it, delete it, update it, and insert a new one), we should be able to do the same thing with an SQL table. Think about a table as something that has properties and can perform actions on records. This sounds like a perfect fit for an object to me. Here are the actions we would want to perform on a table:

* SELECT
    
* UPDATE
    
* DELETE
    
* INSERT
    

But we would also want to limit some of the above actions to just the records we are interested in. So we would need to be able to set the following:

* WHERE and HAVING clauses
    
* LIMIT clause
    
* ORDER BY clause
    
* JOINs
    
* UNIONs
    
* and others!
    

### The Use Case for Active Table

One of the things most database apps do is to allow the user to specify how they want to see the data. Sorting, columns to display, conditions for which record to display, etc. If we have an easy interface to the table object, it is fairly easy to modify the table object to do what the user wants. So dealing with a user becomes setting properties on the table, then performing the action the user wants. Sounds like a good match to me.

### And that is a Design Wrap!

So now we have figured out the requirements for an ORM. As I have said before, I was unable to find an existing ORM that did everything I would like to have in an ORM. I was also not happy with the complexity of some of the existing solutions. Complexity is EVIL and tends to slow things down as it requires more memory and execution time to implement. Given two separate designs that do the same thing, the better design is always the one with fewer parts. This is one of the fundamental rules of engineering.

### Final List of Requirements

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
    

### Takeaways

If you have been following along in this series, you have seen that the more you think about a problem, the clearer the solution becomes. And when you start thinking in terms of objects and actions you can perform on them, you begin to understand the power of an object-oriented design.

The primary thing to remember with any OO design is you are modeling an object with properties. You perform actions on or request things from the object. The object handles the internals of how to do things. The object tries to ensure it returns the correct results given the properties you set. The object shields how it does things from you. If you follow the object's contract (interface), then the object is free to do what it needs to work. The object can also be updated to do things in a better way without affecting things that use the object as long as it holds up its side of the contract.

Another thing to remember with OO design, is you are free to use the object outside of the OO paradigm in other places. You can still do procedural or functional programming and use objects. They are not mutually exclusive.

Next time we can start coding!

**NEXT:** - [PHP Database Cursors](https://blog.phpfui.com/php-database-cursors)

**PREVIOUS:** - [Iterators And Database Cursors In PHP](https://blog.phpfui.com/iterators-and-database-cursors-in-php)