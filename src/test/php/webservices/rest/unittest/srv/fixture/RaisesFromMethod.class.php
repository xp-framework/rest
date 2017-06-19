<?php namespace webservices\rest\unittest\srv\fixture;

/**
 * Fixture for RestContext tests
 *
 * @see  xp://webservices.rest.unitest.RestContextTest
 */
#[@webservice]
class RaisesFromMethod {

  /**
   * Fixture. Raises a `lang.Error`.
   *
   * @return  string
   */
  #[@webmethod(verb= 'GET', path= '/raise/an/error')]
  public function error() {
    throw new \lang\Error('Invocation failed');
  }

  /**
   * Fixture. Raises a `lang.Error`.
   *
   * @return  string
   */
  #[@webmethod(verb= 'GET', path= '/raise/an/exception')]
  public function exception() {
    throw new \lang\IllegalStateException('Invocation failed');
  }
}
