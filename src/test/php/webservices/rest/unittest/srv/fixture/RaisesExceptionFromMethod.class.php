<?php namespace webservices\rest\unittest\srv\fixture;

/**
 * Fixture for RestContext tests
 *
 * @see  xp://webservices.rest.unitest.RestContextTest
 */
#[@webservice]
class RaisesExceptionFromMethod extends \lang\Object {

  /**
   * Fixture. Raises a `lang.Error`.
   *
   * @return  string
   */
  #[@webmethod(verb= 'GET', path= '/raise/an/exception')]
  public function fixture() {
    throw new \lang\Error('Invocation failed');
  }
}
