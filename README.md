REST Client and Server APIs for the XP Framework
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/rest.svg)](http://travis-ci.org/xp-framework/rest)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Required HHVM 3.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_4plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/rest/version.png)](https://packagist.org/packages/xp-framework/rest)

Client
------

### Entry point

The `RestClient` class serves as the entry point to this API. Create a new instance of it with the REST service's endpoint URL and then invoke its `execute()` method to work with the resources.

### Example

Here's an overview of the typical usage for working with the REST API.

```php
use webservices\rest\RestClient;
use webservices\rest\RestRequest;
use peer\http\HttpConstants;

$client= new RestClient('http://api.example.com/');

$request= (new RestRequest('/resource/{id}'))
 ->withMethod(HttpConstants::GET)
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

$resource= $response->data(Person::class);
```

### Authentication

Basic authentication is supported by embedding the credentials in the endpoint URL:

```php
use webservices\rest\RestClient;

$client= new RestClient('http://user:pass@api.example.com/');
```