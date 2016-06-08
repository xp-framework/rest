REST Client and Server APIs for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

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

