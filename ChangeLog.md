REST Client and Server APIs for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

* Fixed fatal error when reading body with unknown media type - @thekid
* Fixed xp-framework/rest#4 - Segments need urlencoding - @thekid

## 6.0.1 / 2015-02-12

* Changed dependency to use XP ~6.0 (instead of dev-master) - @thekid

## 6.0.0 / 2015-10-01

* Improved exception message when required parameters are not found
  (@kiesel, @thekid)
* Implemented special handling of exceptions during REST handler
  construction, see xp-framework/xp-framework#345 (@thekid, @iigorr)
* Heads up: Converted classes to PHP 5.3 namespaces - (@thekid)

