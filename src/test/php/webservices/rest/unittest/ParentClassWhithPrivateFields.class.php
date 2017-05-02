<?php namespace webservices\rest\unittest;

use lang\Object;

/**
 * Class ParentClassWhithPrivateFields
 */
class ParentClassWhithPrivateFields extends ParentOfParentClassWithPrivateFields {

  /** @var string */
  private $field1;

  /** @var string */
  private $field2;

  /**
   * @return string
   */
  public function getField1() {
    return 'getter_'.$this->field1;
  }

  /**
   * @param string $field1
   * @return $this
   */
  public function setField1($field1) {
    $this->field1= $field1;
    return $this;
  }

  /**
   * @return string
   */
  public function getField2() {
    return 'getter_'.$this->field2;
  }

  /**
   * @param string $field2
   * @return $this
   */
  public function setField2($field2) {
    $this->field2= $field2;
    return $this;
  }



}