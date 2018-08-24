REST Client and Server APIs for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 10.0.0 / 2018-08-24

* **Heads up**: Removed possibility to inject log categories via `Logger`
  singleton. Requesting `util.log.LogCategory` instances will now always
  yield the *RestContext* log category.
  (@thekid)
* Made compatible with `xp-framework/logging` version 9.0.0 - @thekid

## 9.1.0 / 2017-11-16

* Made it possible to force arrays and maps to be emitted as JSON objects
  by using an `(object)` cast.
  (@thekid)

## 9.0.1 / 2017-09-24

* Restored `webservices.rest.Rest{Request, Response}`'s toString()
  (@thekid)

## 9.0.0 / 2017-06-20

* **Heads up:** Drop PHP 5.5 support - @thekid
* Added forward compatibility with XP 9.0.0, see PR #24 - @thekid

## 8.3.3 / 2017-05-20

* Refactored code to use `typeof()` instead of `xp::typeOf()`, see
  https://github.com/xp-framework/rfc/issues/323
  (@thekid)

## 8.3.2 / 2017-01-16

* Fixed double-encoding of resolved GET parameters - @aguel, @johannes85

## 8.3.1 / 2017-01-16

* Merged PR #21: Fix GET parameters resolution - @johannes85, @thekid

## 8.3.0 / 2016-10-13

* Merged PR #19: Enum marshalling/demarshalling - @johannes85, @thekid

## 8.2.1 / 2016-10-13

* Merged PR #18: Use rawurlencode() over urlencode() - @kiesel

## 8.2.0 / 2016-09-29

* Changed `Endpoint::with()` to be liberal in what it accepts. It can now
  be called with both a map as single argument and with two arguments, the
  header and its value.
  (@thekid)

## 8.1.1 / 2016-09-27

* Fixed requests with payload *and* query parameters - @thekid

## 8.1.0 / 2016-09-26

* Merged pull request #17: Link header support - @thekid
* Changed `RestRequest` to optionally accept parameters in constructor
  argument uri, adding support to the `resource()` method by doing so.
  (@thekid)

## 8.0.1 / 2016-09-17

* Restored PHP 5.4 compatibility which was officially dropped in 6.4.0.
  Doing so only required changing a single line, so doing it in order
  to increase adoption of 8.0.0
  (@thekid)

## 8.0.0 / 2016-09-04

* Merged PR #16: Resource API. Entry point is `webservices.rest.Endpoint`.
  - New `resource()` method exposes a fluent interface covering typical
    use cases GET, HEAD, POST, PUT, PATCH and DELETE.
  - New `with()` method adds ability to supply omnipresent headers, such
    as *User-Agent* (required by GitHub, for example).
  - Methods now support absolute, relative and full URLs for ease of use
    with HATEOAS links.
  - RestClient class is now deprecated. Migration to new API is typically
    as easy as exchanging *new RestClient(...)* with *new Endpoint(...)*.
    The PR contains an overview of possible caveats you may encounter.
  (@thekid)
* Merged PR #14: Remove deprecated execute() overloading - @thekid

## 7.3.2 / 2016-08-29

* Made compatible with xp-framework/network v8.0.0 - @thekid

## 7.3.1 / 2016-08-28

* Added compatibility with newest xp-framework/http and xp-framework/xml
  releases (both 8.0.0)
  (@thekid)

## 7.3.0 / 2016-08-28

* Added forward compatibility with XP 8.0.0: Use File::in() instead of
  the deprecated *getInputStream()*
  (@thekid)

## 7.2.1 / 2016-07-03

* Fixed problems when using XP6 releases together with PHP 7
  (@thekid)

## 7.2.0 / 2016-06-08

* Merged PR #13: Use xp-forge/json
  (@thekid, @kiesel, @lluchs)

## 7.1.0 / 2016-05-01

* Merged PR #12: Add RestResponse::error(). This new method complements
  the `data()` method in preventing programming mistakes by making error
  handling explicit
  (@thekid, Olaf Seng)

## 7.0.3 / 2016-04-25

* Merged PR #11: Replace raise() with throw - @kiesel

## 7.0.2 / 2016-04-21

* Fixed BC break and accept instances of the deprecated `peer.Header`
  class in RestRequest. See PR #10
  (@thekid)

## 7.0.1 / 2016-03-18

* Fixed double flush() invocation in StreamingOutput - @haimich, @kiesel

## 7.0.0 / 2016-02-21

* **Adopted semantic versioning. See xp-framework/rfc#300** - @thekid 
* Added version compatibility with XP 7 - @thekid
* Deprecated support for wrapper types - @thekid

## 6.4.2 / 2016-01-24

* Fix code to handle baseless objects correctly. See xp-framework/rfc#297
  (@thekid)

## 6.4.1 / 2016-01-24

* Fix code to use `nameof()` instead of the deprecated `getClassName()`
  method from lang.Generic. See xp-framework/core#120
  (@thekid)

## 6.4.0 / 2015-12-20

* **Heads up: Dropped PHP 5.4 support**. *Note: As the main source is not
  touched, unofficial PHP 5.4 support is still available though not tested
  with Travis-CI*.
  (@thekid)

## 6.3.1 / 2015-09-26

* Merged PR #8: Use short array syntax / ::class in annotations - @thekid

## 6.3.0 / 2015-08-22

* Merged xp-framework/rest#7: Feature: Pagination - @thekid
* Merged xp-framework/rest#3: Client-side cookie accessors - @thekid
* Merged xp-framework/rest#5: Request source - @thekid
* Merged xp-framework/rest#6: Support valueOf() with multiple parameters
  (@thekid)

## 6.2.1 / 2015-07-12

* Added forward compatibility with XP 6.4.0 - @thekid

## 6.2.0 / 2015-06-13

* Added forward compatibility with PHP7 - @thekid
* Changed dependency on xp-framework/webservices to ^6.1.0 - @thekid

## 6.1.0 / 2015-06-01

* Added optional message parameter to Response::error(), status(), 
  notFound() and notAcceptable() methods
  (@thekid)
* Fixed fatal error when reading body with unknown media type. This now
  returns a HTTP 415 status code ("Unsupported Media Type")
  (@thekid)
* Fixed xp-framework/rest#4 - Segments need urlencoding - @thekid

## 6.0.1 / 2015-02-12

* Changed dependency to use XP ~6.0 (instead of dev-master) - @thekid

## 6.0.0 / 2015-10-01

* Improved exception message when required parameters are not found
  (@kiesel, @thekid)
* Implemented special handling of exceptions during REST handler
  construction, see xp-framework/xp-framework#345 (@thekid, @iigorr)
* Heads up: Converted classes to PHP 5.3 namespaces - (@thekid)

