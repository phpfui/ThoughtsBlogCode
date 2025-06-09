---
title: "ORM Record Validation in PHP"
datePublished: Mon Jun 09 2025 21:00:16 GMT+0000 (Coordinated Universal Time)
cuid: cmbpkt0qn000d02l4a60lchzx
slug: orm-record-validation-in-php
cover: https://cdn.hashnode.com/res/hashnode/image/stock/unsplash/OfHZYig9SQc/upload/a3dd75123d5b6594447e3d29d8da0e27.jpeg
tags: php, sql, orm, validation, orm-object-relational-mapping

---

One problem with databases is to make sure the data in them is valid. SQL itself offers required fields, typed fields and can be set up to require foreign keys. All of these features of SQL still are not enough to ensure the data in the database is valid. So the developer needs to add additional validation to make sure data going into the database at least makes some sense, and a great way to begin to enforce that is validation in the ORM.

So one of my requirements for my ORM was to implement some basic validation for fields with the ability to extend validation for more specific requirements for unique record types. Let’s see how we might be able to do this in an OO manor.

### Namespaces to the Rescue Again

Just like our Definition namespace, we can use a namespace to put our validators in and by keeping the same base class name, we can automatically associate the correct validator with the proper record class without any additional configuration. So now we have an **\\App\\Record\\Validation** namespace with individual validator classes for each record class. The **Validation** class looks something like this:

```php
namespace App\Record\Validation;
class Member extends \PHPFUI\ORM\Validator
	{
 	/** @var array<string, string[]> */
 	public static array $validators = [
 		'cellPhone' => ['maxlength'],
 		'email' => ['required', 'maxlength', 'email', 'unique'],
 		'firstName' => ['required', 'maxlength'],
		'lastName' => ['required', 'maxlength'],
 		'memberId' => ['integer'],
     	];
 	}
```

As with the **Definition** class, the static $validators array is indexed by field name and contains an array of validator strings. All validators must pass in order for the field to be valid. The email field is a perfect example. It is required (not blank or null), has a maximum length (the field length), must be an email address and it has to be unique in the database.

### Field Comparison Validators

You can compare one field to another on the same **\\App\\Record** with the field validators.

* **gt\_field**
    
* **lt\_field**
    
* **gte\_field**
    
* **lte\_field**
    
* **eq\_field**
    
* **neq\_field**
    

Field validators take another field name as a parameter and perform the specified condition test. To compare against a specific value, use **minvalue**, **maxvalue**, **equal** or **not\_equal**.

### Passing Parameters to Validation Tests

If you follow a validation rule by a colon (:), you can pass a parameter to the validator. Use commas to pass multiple parameters for a validator.

### Putting it all Together in an Example

```php
 	public static array $validators = [
 		'startDate' => ['lte_field:endDate', 'required'],
        'endDate' => ['gte_field:startDate', 'required'],
        'price' => ['minvalue:0', 'not_equal:0', 'required'],
     	];
```

This validator requires both a start and end date and the start date must be less or equal to the end date. It also requires a positive price.

### Unique Parameters

Without any parameters, the **unique** validator will make sure no other record has a matching value for the field being validated. The current record is always exempted from the unique test so it can be updated.

If there are parameters, the first parameter must be a field of the current record. If this is the only parameter, or if the next parameter is also a field of the record, then the unique test is only done with the value of this field set to the current record's value.

If the next parameter is not a field of the record, it is used as a value to match for the preceding field for the unique test.

The above repeats until all parameters are exhausted.

**Example:**

Suppose you have a table with the following fields:

* name
    
* company
    
* division
    
* type
    

You want the name to be unique per company: *unique:company* You want the name to be unique per division with in the company: *unique:company,division* You want the name to be unique for a specific type in the division: *unique:type,shoes,division* You want the name to be unique for a specific type and division: *unique:type,shoes,division,10*

### NOT Operator

You can reverse any validator by preceding the validator with an ! (exclamation mark).

**Example:** !starts\_with:/ will fail if the field starts with a /

### Extending the Validation class

By extending the **\\PHPFUI\\ORM\\Validator** class with a custom class, you can add any validator you want. A validator is defined by a method with the following signature:

**validate\_TESTNAME(mixed $value) : string**

The TESTNAME is the name in the array of validator strings. It is passed the value of the field that needs validation. An empty string is returned on a successful validation, or an error message is returned. The error message should be as clear as possible including the expected values and the actual value passed.

Suppose we wanted a proper name rule. This rule would require the first character to be upper case and at least one following character in lower case. Here is a brute force implementation:

```php
class ProperNameValidator extends \PHPFUI\ORM\Validator
    {
	protected function validate_proper_name(mixed $value) : string
		{
        $value = (string)$value;
        $length = strlen($value);
        $firstUpper = $hasLower = false;
        for ($i = 0; $i < $length; ++$i)
            {
            $ch = $value[$i];
            if (ctype_upper($ch))
                {
                if (! $i)
                    {
                    $firstUpper = true;
                    }
                }
            else if (ctype_lower($ch) && $i)
                {
                $hasLower = true;
                break;
                }
            }

		return $this->testIt($firstUpper && $hasLower, 'proper_name', ['value' => $value]);
		}
    }
```

Then our record **Validation** class would look like this:

```php
class Person extends ProperNameValidator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'firstName' => ['proper_name', 'required', 'maxlength'],
		'lastName' => ['proper_name', 'required', 'maxlength'],
	    ];
	}
```

### Translations

The validator assumes all errors will be translated, but I will leave that up to the reader to configure [PHPFUI/translation](https://packagist.org/packages/phpfui/translation)

## The Takeaways

Use inheritance to make something more specific. In this case, we wanted a more specific rule than the base class provided. We were able to extend the class and use it instead of the base class.

Leave classes open for inheritance if there is something that you don’t want to implement, but someone else may.

**PREVIOUS: -** [**Implementing Active Records in PHP - Part 2**](https://blog.phpfui.com/implementing-active-records-in-php-part-2)