<?php namespace webservices\rest\unittest\srv\fixture;

/**
 * Fixture for RestContext tests
 *
 * @see  xp://webservices.rest.unitest.RestContextTest
 */
#[@webservice]
class RaisesExceptionFromConstructor extends \lang\Object {

  /**
   * Constructor. Raises a `lang.IllegalStateException`.
   */
  public function __construct() {
    throw new \lang\IllegalStateException('Cannot instantiate');
  }
}
