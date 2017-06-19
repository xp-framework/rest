<?php namespace webservices\rest\unittest\srv\fixture;

use util\Money;
use util\Objects;
use lang\Value;

class Wallet implements Value {
  public $values= [];

  public function __construct($values= []) {
    $this->values= $values;
  }

  public function add(Money $m) {
    $this->values[]= $m;
    return $this;
  }

  public function toString() {
    return nameof($this).'@'.Objects::stringOf($this->values);
  }

  public function hashCode() {
    return 'W'.Objects::hashOf($this->values);
  }

  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->values, $value->values) : 1;
  }
}
