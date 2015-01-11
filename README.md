REST Client and Server APIs for the XP Framework
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/rest.svg)](http://travis-ci.org/xp-framework/rest)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Required HHVM 3.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_4plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/rest/version.png)](https://packagist.org/packages/xp-framework/rest)

Client
------

### Entry point

The `RestClient` class serves as the entry point to this API.
Create a new instance of it with the REST service's endpoint URL and
then invoke its `execute()` method to work with the resources.

### Example

Here's an overview of the typical usage for working with the REST API.

```php
$client= new RestClient('http://api.example.com/');

$request= new RestRequest('/resource/{id}');
$request->addSegment('id', 5000);          // Replaces token in resource
$request->addParameter('details', 'true'); // POST or querystring

$response= $client->execute($request);
$content= $response->content();            // Raw data as string
$value= $response->data();                 // Deserialize to map
```

### Automatic deserialization

The REST API supports automatic result deserialization by passing
a `lang.Type` instance to the `data()` method.

```php
$type= XPClass::forName('com.example.api.types.Resource');
$resource= $client->execute($request)->data($type);
```

### Authentication

Basic authentication is supported by embedding the credentials in the
endpoint URL:

```php
$client= new RestClient('http://user:pass@api.example.com/');
```

### Fluent interface

The `RestRequest` class provides a fluent interface:

```php
$request= (new RestRequest('/resource/{id}'))
 ->withMethod(HttpConstants::GET)
 ->withSegment('id', 5000)
 ->withParameter('details', 'true')
 ->withHeader('X-Binford', '6100 (more power)'
;
```
