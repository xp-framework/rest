REST Client and Server APIs for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

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

