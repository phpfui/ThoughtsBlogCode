---
title: "Iterators and Database Cursors in PHP"
datePublished: Mon Jun 26 2023 16:59:27 GMT+0000 (Coordinated Universal Time)
cuid: cljd3t43a000009l8ea8oeovn
slug: iterators-and-database-cursors-in-php
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/Wpnoqo2plFA/upload/6a61fdab6e7733c7def77c249cdc19da.jpeg
tags: oop, php, orm, iterator, iterator-design-pattern

---

In [my last post](https://blog.phpfui.com/zen-and-the-art-of-class-design), I was talking about designing classes and identified some requirements for an ORM I could envision but not find an implementation for. I laid out some requirements, but I don't think I completely thought out what was needed for a nice ORM implementation, so I thought instead of diving into code, I would do some more design work. Let's start!

### More Requirements

One of the things you need to do with databases is query them and return records matching your query, or why else would you have a database? In PHP land we iterate over things all the time with loops, primarily **foreach** loops. The **foreach** statement can iterate over anything that implements the Iterator interface. This includes arrays and since PHP arrays are a great data structure, we tend to use a lot of arrays in PHP development. But what if what we want is not already in an array? What if it exists as a file on the disk, or records in a table? How can we use the handy **foreach** loop to look at all the records contained in them?

### Iterator Interface to the Rescue

Fortunately, the smart folks at PHP defined the [Iterator interface](https://www.php.net/manual/en/class.iterator.php) to allow **foreach** loops to interact with our custom code. Here is the definition of the Iterator interface.

```php
interface Iterator extends Traversable {
  public current(): mixed
  public key(): mixed
  public next(): void
  public rewind(): void
  public valid(): bool
}
```

Let's take a look at how this OO abstraction can be leveraged for the **foreach** loop.

### Interfaces are Contracts

The interface defines 5 methods. Here is what they do:

* **current()** returns the record at the current position of the iterator. This can return anything, including a scalar type, an array, an object or anything PHP can create.
    
* **key()** returns the key for the **foreach** loop, normally this would be the array key.
    
* **next()** moves the iterator to the next element. Notice that it does not return anything, it simply moves the current record pointer. You need to call current() again to know what the iterator is currently pointing to.
    
* **rewind()** returns the current pointer to the start.
    
* **valid()** returns true if the iterator is pointing to a real record. For an empty dataset, this would return false. Valid also returns false when the iterator hits the end of the list.
    

This is all a **foreach** loop needs to traverse a list. If we implement the interface, we can now use a **foreach** loop to read through any data we want. The interface defines a contract with the user. If you follow the contract of the methods and how they work, then your class will work wherever an Interface is required.

### Iterators for CSV Files

Say we have a CSV (comma-separated values) file on the disk. Here is how we would read it with a CSV iterator:

```php
$csvReader = new \CSV\FileReader('countries.csv');
foreach ($csvReader as $row)
  echo $row['Country'] . "\n";
```

Notice how the $csvReader looks exactly like an array. Let's number the list:

```php
$csvReader = new \CSV\FileReader('countries.csv');
foreach ($csvReader as $index => $row)
  echo "{$index}. {$row['Country']}\n";
```

Here is the code behind the \\CSV\\FileReader:

```php
namespace CSV;
class FileReader extends \CSV\Reader
	{
	public function __construct(private readonly string $fileName, bool $headerRow = true, string $delimiter = ',')
		{
		parent::__construct($headerRow, $delimiter);
		}
	protected function open() : static
		{
		if (\file_exists($this->fileName))
			{
			if ($this->stream)
				{
				\fclose($this->stream);
				$this->stream = null;
				}
			$this->stream = @\fopen($this->fileName, 'r');
			}
		return $this;
		}
	}
```

Notice the constructor takes a required file name. This makes sense since we can't read a CSV file if we don't know what the file name is. We also have two default parameters that users of our class may be interested in. The $headers boolean defaults to true and indicated the CSV file has a header row naming the columns. Most CSV files follow this format since unnamed columns tend to be confusing to people you might need to read your file. The last parameter is the delimiter character. Since CSV files are normally delimited by commas, this is the default. But some files might use another character such as a tab, this allows the user to specify an alternate field delimiter.

The other method is **open()** which understandably opens the file for reading since that is what we want to do with the file. Notice open() is not in the Iterator interface. This is an addition to our class that we need for it to function correctly.

But what about the 5 methods in the original interface? Where are they? How can this work?

### Enter Hoisting

One of the benefits of OOP is reuse and the ability to change just the things we need, and not worry about the other parts which should just work if we follow the intended interface contracts. In this case, we hoisted all the basic Iterator methods into the parent class here:

```php
namespace CSV;
abstract class Reader implements \Iterator
	{
	protected $stream = null;	
	private array $current = [];
	private array $headers = [];
	private int $index = 0;
	public function __construct(private readonly bool $headerRow = true, private readonly string $delimiter = ',')
		{
		$this->rewind();
		}
	public function current() : array
		{
		return $this->current;
		}
	public function key() : int
		{
		return $this->index;
		}
	public function next() : void
		{
		$this->current = [];
		if ($this->stream)
			{
			$array = \fgetcsv($this->stream, 0, $this->delimiter);
			if ($array)
				{
				++$this->index;
				if ($this->headers)
					{
					foreach ($this->headers as $index => $header)
						{
						if (isset($array[$index]))
							{
							$this->current[$header] = $array[$index];
							}
						else
							{
							break;
							}
						}
					}
				else
					{
					$this->current = $array;
					}
				}
			}
		}
	public function rewind() : void
		{
		$this->index = -1;
		$this->open();
		if ($this->stream)
			{
			\rewind($this->stream);
			if ($this->headerRow)
				{
				$this->headers = \fgetcsv($this->stream, 0, $this->delimiter);
				}
			}
		$this->next();
		}
	public function setHeaders(array $headers) : static
		{00
		$this->headers = $headers;
		return $this;
		}
	public function valid() : bool
		{
		return $this->current && $this->stream;
		}
	protected function open() : static
		{
		return $this;
		}
	}
```

Notice I implemented all 5 of the Iterator methods in this parent class. The class is abstract because it is not usable by itself unless the $stream property is properly initialized. In our FileReader class, we used the open() method to initialize the $stream from a file. In the base class, we know we need an open() method, but we don't know how it will work. We don't want to force our users to implement it because there might be a case where it does not make sense for them. So we just implement a method that does nothing and can be redefined later if needed.

Also, notice we added a setHeaders() method. If you wanted to change headers at some point in processing a stream, you could do that.

The major work in this class is being performed by the next() method. Since PHP provides us with a nice line reader for CSV files, I decided to use it. fgetcsv() uses a resource stream as input, so that is why our class assumes a resource stream.

The other work done in the class is the rewind() method. Notice that it is called in the constructor to initialize the object. This guarantees the object is created correctly and in a valid state when the constructor is done. Calling other methods in your constructor is good practice as custom one-off code might get out of sync with the rest of the class if a future modification changes some behavior. This way, we know rewind will always get to the beginning of the stream and read in the first record (if it exists).

The rest of the class is straightforward code returning the current state of our reader.

### Implementing other Interfaces

Since our base class deals with generic streams, what else could we iterate over? How about just a generic steam from who knows where:

```php
namespace CSV;
class StreamReader extends \CSV\Reader
	{
	public function __construct($stream, bool $headerRow = true, string $delimiter = ',') // @phpstan-ignore-line
		{
		$this->stream = $stream;
		parent::__construct($headerRow, $delimiter);
		}
	}
```

That was simple! This OOP stuff is starting to make sense! Notice we only need to initialize the $stream property. We then call the parent constructor and we are good to go. Now anything that is a resource can be iterated over as a CSV file.

**NOTE:** I did not give $stream a type. This is because PHP has not provided a built-in type for resources due to various reasons. So we have to go with an untyped variable, which is not ideal, but it works. \\CSV\\Reader will probably blow up if passed the wrong thing, but it will be obvious to the developer who does not read the docs. Also, I removed the docs from the sample code to save space. You can see the full source [here](https://github.com/phpfui/ThoughtsBlogCode/tree/main/src/CSV).

Another fun thing we can do with stream resources in PHP is convert a string into a stream. Let's see how that looks:

```php
namespace CSV;
class StringReader extends \CSV\Reader
	{
	public function __construct(private readonly string $data, bool $headerRow = true, string $delimiter = ',')
		{
		parent::__construct($headerRow, $delimiter);
		}
	protected function open() : static
		{
		if ($this->stream)
			{
			\fclose($this->stream);
			}
		$this->stream = \fopen('php://memory', 'r+');
		\fwrite($this->stream, $this->data);
		return $this;
		}
	}
```

The first thing to notice is we now need the open() method, which we did not need in the StreamReader class. The reason is our constructor takes the string we want to process as a CSV. We keep a copy of the string since we know that rewind() is going to be called, and it needs the original string. We do all the magic in the open() method, which is called from the rewind() method. By doing this, we are keeping the contract of the class. The constructor rewinds the object and calls open() to make sure it is initialized correctly. The open() method does the work to convert a string to a stream.

### Advantages of Iterators over Arrays

As you can see in the \\CSV\\Reader code, we never have more than one row of the stream in memory at one time. This is the primary advantage of Iterators over arrays. Since we don't have a large memory footprint, we can iterator over huge datasets that may not fit into memory at one time. The interface allows us to keep the semantics of an array but without the memory overhead of the array. We can take existing array logic and convert it to iterators to save memory but not have to change the logic, except for the source of the array, which is generally a very small change.

### But what does this have to do with an ORM?

I originally started this post by wanting to further think about what I would want in an ORM. Database work tends to involve large datasets, and we certainly don't want to read all rows of a table into an array in memory as we can't be sure they will all fit! So the Iterator interface looks like a good solution to the problem. And this is one of the concepts lacking in most ORMs I looked at before I decided to write my own ORM. Most ORMs are repository based, which tend to be in memory collections of objects from the database. They also tend to consume huge amounts of memory because of this.

The final deciding factor is that we don't have to return an array from the current() method. We can return any object in PHP. What if that object is an Active Record? Now we can iterate over a huge dataset and do want ever we want with individual records.

There is still more ORM design work to do which I will cover next time. Till then, here are the takeaways for now. You can also follow along with the fully commented and tested code [here](https://github.com/phpfui/ThoughtsBlogCode/tree/main/src/CSV).

### Takeaways

* **Interfaces define contracts**. Implement the interface and you can use the class anywhere the interface is used.
    
* **Hoist common code** up to the parent.
    
* **Think about how things are the same or different**. Hoist common code to the parent class, and implement different things in the child classes.
    
* **Add functionality** where it makes sense.
    

**NEXT:** - [Active Table Design Pattern](https://blog.phpfui.com/active-table-design-pattern)

**PREVIOUS:** - [Zen And The Art Of Class Design](https://blog.phpfui.com/zen-and-the-art-of-class-design)