<?php namespace webservices\rest;

use util\Objects;


/**
 * Represents the REST payload
 *
 * @test  xp://net.xp_framework.unittest.webservices.rest.PayloadTest
 */
class Payload {
  public $value, $properties;

  /**
   * Creates a new payload instance
   *
   * @param   var value
   * @param   [:string] properties
   */
  public function __construct($value= null, $properties= []) {
    $this->value= $value;
    $this->properties= $properties;
  }

  /**
   * Returns whether a given value is equal to this payload
   * 
   * @param  var cmp
   * @return bool
   */
  public function equals($cmp) {
    return (
      $cmp instanceof self && 
      Objects::equal($cmp->value, $this->value) &&
      $this->properties === $cmp->properties
    );
  }
}
