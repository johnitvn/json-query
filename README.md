Json Query
=============
[![Latest Stable Version](https://poser.pugx.org/johnitvn/json-query/v/stable)](https://packagist.org/packages/johnitvn/json-query)
[![License](https://poser.pugx.org/johnitvn/json-query/license)](https://packagist.org/packages/johnitvn/json-query)
[![Total Downloads](https://poser.pugx.org/johnitvn/json-query/downloads)](https://packagist.org/packages/johnitvn/json-query)
[![Monthly Downloads](https://poser.pugx.org/johnitvn/json-query/d/monthly)](https://packagist.org/packages/johnitvn/json-query)
[![Daily Downloads](https://poser.pugx.org/johnitvn/json-query/d/daily)](https://packagist.org/packages/johnitvn/json-query)

A PHP library to parser, create, edit, query and do validate [JSON](http://www.json.org/).


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist johnitvn/json-query "*"
```

or add

```
"johnitvn/json-query": "*"
```

to the require section of your `composer.json` file.


Usage
-----

The library is intended to be used with nested json structures, or with json data that needs validation. Or in any situation where you would find it is easier to do something like this:

````php
$document = new johnitvn\jsonquery\JsonDocument();
$document->addValue('/path/to/nested/array/-', array('firstName'=> 'Fred', 'lastName' => 'Blogg'));
$json = $document->toJson(true);
````

which will give you the following json:

````json
{
    "path": {
        "to": {
            "nested": {
                "array": [
                    {
                        "firstName": "Fred",
                        "lastName": "Blogg"
                    }
                ]
            }
        }
    }
}

````

You can query this value by calling:

````php
$person = $document->getValue('/path/to/nested/array/0');
````

and update it with:

````php
$document->addValue('/path/to/nested/array/0/lastName', 'Bloggs');
````

and move it with:

````php
$document->moveValue('/path/to/nested/array/0', '/users/-');
$document->tidy();
$json = $document->toJson(true);
````

to end up with:

````json
{
    "users": [
        {
            "firstName": "Fred",
            "lastName": "Bloggs"
        }
    ]
}
````

then delete it with:

````
$document->deleteValue('/users/0');
````

Json Query includes an implementation of JSON Schema, version 4. This allows you to validate your data. The following example schema describes an array containing objects whose properties are all required and whose types are defined.

````php
$schema = '{
    "items": {
        "properties": {
            "firstName": {"type": "string"},
            "lastName": {"type": "string"}                  
        },
        "required": ["firstName", "lastName"]
    }
}';
````

Now when you try to add values, Jquery Query will only do so if they are valid. So you have to check.

````php
$document->loadSchema($schema);

$result = $document->addValue('/-', array('firstName'=> 'Fred', 'lastName' => 'Bloggs'));
# true

$result = $document->addValue('/-', array('firstName'=> 'Fred', 'lastName' => 3));
# false, lastName is not a string

$result = $document->addValue('/0', array('firstName'=> 'Fred'));
# true, required values are not checked when we are building

# but are checked if we validate directly

$result = $document->validate();
# false - required lastName is missing
````

Without a schema, any value can be added anywhere, of course

