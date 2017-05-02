<?php namespace webservices\rest\unittest;

use lang\Object;

/**
 * Class ParentOfParentClassWithPrivateFields
 */
class ParentOfParentClassWithPrivateFields extends Object {

  /** @var string */
  private $field3;

  /**
   * @return string
   */
  public function getField3() {
    return $this->field3;
  }

  /**
   * @param string $field3
   */
  public function setField3($field3) {
    $this->field3= $field3;
  }

}