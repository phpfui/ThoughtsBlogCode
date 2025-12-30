---
title: "Types Matter!"
datePublished: Tue Dec 30 2025 15:55:01 GMT+0000 (Coordinated Universal Time)
cuid: cmjsrq8tp000a02jm9y4lamea
slug: types-matter
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/4JPXmg-N3sg/upload/81ede9b366cd96f577d7298a3e4e94ec.jpeg
tags: php, types

---

Often programmers coming from type less languages like JavaScript or other simple scripting languages don’t see the advantage of types. After all, they wrote all the code in their world and know how it works. Types just seem to be overly picky and cause annoying warning messages when you try to assign a string to something you declared as an integer. After all, the string just consists of the digits 0-9, just convert it to a number and continue!

PHP, as did most scripting languages, started off without declared types. Scripting languages were always seen is trivial languages to solve trivial problems, and typed variables don’t add much to a 10 line program. JavaScript is probably the most obvious example of this. Who needs types when all you are going to do is modify the DOM when a user clicks a button? But as simple scripting languages evolved and grew into full front and back end solutions, and code base grew exponentially, a funny thing started to happen. Scripting languages started adding declared types!

PHP added class types to parameters in 5.0, then scalar parameter types in PHP 7.1, and even return types in PHP 7.1. In PHP 7.4, typed member properties where introduced and PHP 8.1 added enumerated types. While JavaScript added more defined classes, it took a whole new language, TypeScript, to really endorse types in the JavaScript community. If you study the history of computer languages, you will find this even applies to complied languages. FORTRAN IV had implicit types based on the first letter of the variable name. Variables that started with I, J, K, L, M or N where integers, and everything else was a REAL (or floating point). Newer versions of Fortran (now in mixed case!) include abstract types and even pointers! The transition from C to C++ also introduced much stricter typing.

### But Why Bother With Types?

This is a common question from those developers who have never used a strictly typed language. In my experience, if you were introduced to strict types when learning computer languages, you will always be a strict type developer, but if you learned programming in with out strict typing, it takes lots of hard learned lessons in failure to finally understand types. The bottom line is types help you create verifiably valid programs with fewer surprises and gives you a higher degree of confidence in your code to work correctly.

But first you need to understand what types are, and then it will become more apparent how types help you and are not a hindrance to better code.

### Implicit Types

All computer languages (with the exception of assembly languages) have implicit types. An implicit type is how the language represents the underlying 1’s and 0’s at a specific memory location to you, the programmer. Is the data a 32 bit integer, or a 4 character ANSI string? Should I treat this 64 byte piece of memory as a pointer to another memory location, or treat it as a floating point number? Implicit types are most pronounced when we output them to our users. Should I display this value as a series of characters on the screen, or should I format it as an integer or a number with a decimal point? In PHP, the interpreter decides the data type (int, float, string, bool) based on when the variable is assigned. The following are all different implicit types, and may display in the same way, but are all different.

```php
$a = 1;
echo $a;
$b = true;
echo $b;
$c = '1';
echo $c;
$d = 1.0;
echo $d;
```

The output of the above is `1111`, but variable has a different underlying representation, they just look the same when output.

### Implicit Conversions

Scripting languages hide the implementation differences from the programmer with implicit conversions between types. In PHP, this most obvious when comparing variables. Both PHP and JavaScript have the notion of equality and a strict equality. All the above variables will evaluate to equal if you use the `==` operator, but will not be equal with the strict type `===` operator, where not only do the values have to be the same, but the types need to be the same.

Another fall out from implicit type conversions is doing math with strings. `’10’ * 5` will print as `50` since the string `’10’` gets converted to an integer 10 before it is multiplied by 5. While this may be what you want in most cases, it would help if the language would let you know you did this rather than just doing this behind your back!

As your program grows larger, you start to forget about implicit requirements of your older code. Should this variable always be an integer? Maybe it should be a float? You could cause a rounding error by implicitly converting a float to an integer.

### The Advantages of Types

1. **Correct Math**
    
    Rounding errors are your biggest problem here. If you are dealing with money, rounding makes a financial difference. In the sciences, floating point is common, and you don’t want to accidentally truncate to an integer.
    
2. **Correct Comparisons**
    
    While you can use `===` for equality comparisons, PHP does not have equivalents for less or greater than. You take your chances that implicit conversion will do what you want in all cases. Better to get the types correct to begin with.
    
3. **Correct Assignments**
    
    A common error is assigning a different type to a variable. This can cause subtle issues later on in the program (see above). It is better to catch these types of errors before bugs manifest themselves much later in the execution path.
    
4. **Correct Arguments to Functions and Methods**
    
    Often you are calling functions or methods that either you did not create, or you wrote a long time ago. Parameter types help you understand the requirements of the method and help insure your usage is more correct.
    
5. **Notes to Future Maintainers**
    
    Types are great for documenting things for future maintainers, or even your future self. Without types, you have no idea of what is required. Is $id an integer, a UUID, or an actual object? Declared types are documentation!
    
6. **Easier Refactoring**
    
    And perhaps the biggest advantage of types is easier refactoring. In PHP, you can use tools like [PHPStan](https://phpstan.org/) to help with type checking your code. When you refactor, PHPStan can help find places you forgot to check from changes caused by the refactoring. Compiled languages with strict type checking will not complete compiling with type errors. And the compiler looks at the entire program, not just one execution path. Developers with compile time strict type checking will tell you this is a huge advantage. I personally have done large refactoring projects with C++ and PHP. In C++, most of my refactoring works the first time once compiled. And the project can move forward quickly with a better design. But in PHP, it takes a lot longer to work through all the issues, even with PHPStan, to get to the better design. And forget about anything but trivial refactoring of JavaScript.
    
7. **Better Confidence Your Code Does What You Think It Should Do**
    
    Besides refactoring, which hopefully you will never have to do that much, stricter typing gives you the confidence that your code is correct. Stupid type errors are caught very early in the development process so they don’t produce unexpected results at 3am in the morning. But this confidence only happens when you consistently apply types everywhere you can, and make them as strict as possible. The more loosely typed something is, the easier it is for subtle bugs to creep into your code base.
    

### Tips for Adding Types to PHP

1. **Always Type All Parameters**
    
    This is almost a requirement for any serious PHP developer. Since PHP 7.0 we have been able to type parameters. Not only does this document the code automatically, PHP and PHPStan can catch obvious errors in your logic. And the stricter you can make the types, the more correctly the logic can be validated.
    
2. **Always Type All Return Types**
    
    Functions and methods should always specify return types for the same reason. This helps the calling logic to better understand what is coming back from the method.
    
3. **Use Typed Objects (classes) Where Ever Practical**
    
    Typed objects (from classes) have a huge advantage in type checking, as the class itself can verify it’s own correctness, and passing a fully type object to another class or method further ensures the program executes as you would expect.
    
4. **Initialize Local Variables Before Use**
    
    I always initialized a variable to the correct type before I use it when ever possible. Just setting a variable to the correct type is often enough to catch errors later in the code.
    
5. **Initialize as Much as Possible in the Constructor**
    
    Since properties can now be fully typed, make sure they are all set to the correct types in the constructor or property declaration. This helps insure the object is valid and properly constructed.
    

### PHP still has a way to go with Types

While I love PHP, it still has a ways to go with types. One of the beauties of PHP is implicit typing means your code can be simpler and more flexible, but the downside of that is allowing errors to creep in at runtime or while refactoring. Some languages are completely strictly statically typed and this creates other issues with creating generic reusable code. Modern languages like C++, C# and TypeScript use the concept of “templates” which are classes with substitutable types. But this requires a more complex type system and complex source code. PHP allows for the same thing, but just without any strict type checking, not ideal, but much simpler code.

I think PHP can go further with types and In my next post I will propose a better type syntax that will move PHP forward with types, but not into the realm of strictly typed compiled languages like C++ and C#. But it will help ensure our PHP is more strongly typed and able to catch stupid type errors, which is the entire reason for type checking to begin with.

**PREVIOUS: -** [PHP ORM Wrapup and Benchmarks](https://blog.phpfui.com/php-orm-wrapup-and-benchmarks)

**A SIDE BAR: -** [Digital Computers and Digitization](https://blog.phpfui.com/digital-computers-and-digitization)