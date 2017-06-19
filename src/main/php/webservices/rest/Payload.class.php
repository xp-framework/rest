<?php namespace webservices\rest;

use util\Objects;
use lang\Value;

/**
 * Represents the REST payload
 *
 * @test  xp://net.xp_framework.unittest.webservices.rest.PayloadTest
 */
class Payload implements Value {
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

  /** @return string */
  public function hashCode() {
    return 'O'.Objects::hashOf([$this->value, $this->properties]);
  }

  /** @return string */
  public function toString() {
    $p= '';
    foreach ($this->properties as $key => $value) {
      $p.= ', '.$key.'= '.$value;
    }
    return nameof($this).'('.substr($p, 2).")@{\n".str_replace("\n", "\n  ", Objects::stringOf($this->value))."\n}";
  }

  /**
   * Compares this output to a given value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self
      ? Objects::compare([$this->value, $this->properties], [$value->value, $value->properties])
      : 1
    ;
  }
}
