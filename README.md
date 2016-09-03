REST Client and Server APIs for the XP Framework
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/rest.svg)](http://travis-ci.org/xp-framework/rest)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.5+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_5plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Required HHVM 3.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_4plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/rest/version.png)](https://packagist.org/packages/xp-framework/rest)

Client
------

### Entry point

The `RestClient` class serves as the entry point to this API. Create a new instance of it with the REST service's endpoint URL and then invoke its `execute()` method to work with the resources.

### Creating: post

```php
$client= new RestClient('http://api.example.com/');
$response= $client->post('users', ['name' => 'Test'], 'application/json');

// Check status codes
if (201 !== $response->status()) {
  throw new IllegalStateException('Could not create user!');
}

// Retrieve response headers
$url= $response->header('Location');
```

### Reading: get / head
```php
$client= new RestClient('http://api.example.com/');

// Unmarshal to object by optionally passing a type; otherwise returned as map
$user= $client->get('users/self')->data(User::class);

// Test for existance with HEAD
$exists= (200 === $client->head('users/1549')->status());

// Pass parameters
$list= $client->get('users', ['page' => 1, 'per_page' => 50])->data();
```

### Updating: put / patch
```php
$client= (new RestClient('http://api.example.com/'))
  ->using(RestFormat::$JSON)
  ->accepting(RestFormat::$JSON)
;

// Default content type and accept types set on connection used
$updated= $client->put('users/self', ['name' => 'Tested', 'login' => $mail])->data();

// Can also use PATCH - typically used to modify only parts of the resoure
$updated= $client->patch('users/self', ['name' => 'Changed'])->data();
```

### Deleting: delete
```php
$client= new RestClient('http://api.example.com/');

// Pass segments
$client->delete(['user/{id}', 'id' => 6100]);
```

If you need to customize the request beyong the typical uses, use the `execute()` method.

```php
use webservices\rest\RestClient;
use webservices\rest\RestRequest;

$client= new RestClient('http://api.example.com/');

$request= (new RestRequest('resource/{id}'))
 ->withMethod('GET')
 ->withSegment('id', 5000)
 ->withParameter('details', 'true')
 ->withHeader('X-Binford', '6100 (more power)'
;

$response= $client->execute($request);
$content= $response->content();            // Raw data as string
$value= $response->data();                 // Deserialize to map
```

### Deserialization

The REST API supports automatic result deserialization by passing a type to the `data()` method.

```php
use com\example\api\types\Person;

$person= $response->data(Person::class);
$strings= $response->data('string[]');
$codes= $response->data('[:int]');
```

### Authentication

Basic authentication is supported by embedding the credentials in the endpoint URL:

```php
use webservices\rest\RestClient;

$client= new RestClient('http://user:pass@api.example.com/');
```