---
title: "The Genius of PHP"
datePublished: Tue Jan 18 2022 23:11:00 GMT+0000 (Coordinated Universal Time)
cuid: ckykqcjvc08g3tos1dmpqfrj6
slug: the-genius-of-php
cover: https://cdn.hashnode.com/res/hashnode/image/upload/v1642639986146/X75HcT31f.png
ogImage: https://cdn.hashnode.com/res/hashnode/image/upload/v1642607125763/kpGTKezww.png
tags: php

---

PHP has a long history and no doubt its share of haters for one reason (pre PHP 5.3 for example) or another (weird function parameter orders? Really, get over yourself!). But in reality, PHP is the most popular web programming language on the internet with about 80% market share, so it must be doing something right.

And it does a lot of things right, which gets us to the point of my first blog entry on HashNode! The early design decisions in PHP were key to its early and widespread adoption.

Probably the most important thing to know about PHP is each request starts with a blank slate. The very first file PHP loads has no prior history to it. It is a completely literally blank page. There is no server behind the scenes running code for you or setting you your environment. Well, actually php.ini does some work setting up limits (like memory size, available functionality in terms of available modules, and PHP keeps track of a unique number called a session id), but there is no server remembering your last database access, signed in users, or application state.

The other thing to know about the blank slate is, PHP throws away everything your PHP script has done except if you specifically remember to save things off before the last line of PHP executes. Now some things PHP helps you manage, like cookies and sessions, but for the most part, you are on your own in terms of what you need to get and what you want to save.

This blank server approach is a two edged sword. On one hand, you don't have to worry about what came before you, or dealing with the refuse you create in rendering a page. One of the common results of this in the PHP development community is the lack of attention to the cleanup of code, for example deleting temporary files and releasing handles. For the most part, these are not huge issues and the server compensates and does a lot of work cleaning up after sloppy PHP developers. But on the other hand, you don't get any leg up on the page creation, so you end up having to start everything from scratch every time. And since PHP is an interpreted and not compiled language, run times can suffer from this design. I will be going over many ways to extract the maximum performance out of PHP in subsequent posts, but just be aware that super heavy duty frameworks will be slow due to their "be everything to everybody" design philosophy.

Another genius feature of PHP is that it supports both the procedural and object oriented (OO) methods of development. In PHP, not everything is an object. You can write completely in a procedural manor and never have to touch an object if you want. This is a great feature for people new to writing code, as the OO approach requires a different mental model and the procedural model is much simpler to comprehend. This is in to stark contract to Java, Ruby and other languages that insist on classes and objects.

PHP also can be very forgiving of bad code with the lack of enforcement of types, declarations, and on the fly objects. It is also a scripting language, meaning you don't have to compile it to run it. Save the PHP file to disk and test it. This allows new developers to quickly get some code working and provide instant feedback. This is in comparison to C# or Java which require a compilation step, and in addition for C#, another wait while the web server reloads the complete C# application before a page can be served.

Another feature of PHP that led to large scale adoption was PHP in the past played fast and loose with types, declarations and objects. This meant code would probably appear to function even if it had several hidden bugs. While this is great for getting something up quickly (a big feature in web development), in the long run it makes maintaining large PHP projects harder. The good news is the PHP team has been slowly patching these holes, which as made PHP evolve over the years to become a better language. With PHP 5.0, you had a better object system with constructors, destructors, public, protected, and private properties and methods, interfaces, abstract classes, class based type hints for function and method parameters and static propertied and methods. Then in PHP 7.0, they added the ability to type scalar (int, string, bool, array, float, ...) parameter types as well. This was a huge improvement, as you now had a language construct to help you figure out what to pass to functions and methods. The next step in PHP 7.4 was to apply types to member properties (variables that belong to the entire class). All these added types help insure your program behaves as you expect, otherwise you can get an error or warning.

Since PHP 5.3, [Composer](https://getcomposer.org/) and [Packagist.org](https://packagist.org/) have enabled a huge user community of open source projects that allow developers to find solutions to common problems quickly.

All these things improved PHP's ability to work in large code bases, but still PHP retained the ease of use for beginner developers. All these things added up to a fairly easy language for novice and experienced developers to easily create web pages. And thus, PHP became the dominate web language.

In my next post, I am going to cover the absolutely most genius feature of PHP ever! In fact, it is so ingenious that very few languages implement this feature, although there is nothing but inertia to keep any of them from adopting it. And it this feature that makes it super easy to write PHP code.

**NEXT:** \- [The Attack of the 50 Mile Wide Open Source Supply Chain](https://blog.phpfui.com/the-attack-of-the-50-mile-wide-open-source-supply-chain)