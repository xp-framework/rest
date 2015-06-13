<?php namespace webservices\rest\unittest;

use webservices\rest\RestMarshalling;
use unittest\actions\RuntimeVersion;
use lang\Primitive;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestMarshalling
 */
#[@action(new RuntimeVersion('<7.0.0-dev'))]
class RestWrapperTypesMarshallingTest extends \unittest\TestCase {
  private $fixture;

  /**
   * Sets up test case
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= new RestMarshalling();
  }

  #[@test]
  public function marshal_string_wrapper_object() {
    $this->assertEquals('Hello', $this->fixture->marshal(new \lang\types\String('Hello')));
  }

  #[@test]
  public function marshal_char_wrapper_object() {
    $this->assertEquals('A', $this->fixture->marshal(new \lang\types\Character('A')));
  }

  #[@test]
  public function marshal_string_wrapper_object_unicode() {
    $this->assertEquals('Übercoder', $this->fixture->marshal(new \lang\types\String("\303\234bercoder", 'utf-8')));
  }

  #[@test]
  public function marshal_long_wrapper_object() {
    $this->assertEquals(61000, $this->fixture->marshal(new \lang\types\Long(61000)));
  }

  #[@test]
  public function marshal_int_wrapper_object() {
    $this->assertEquals(6100, $this->fixture->marshal(new \lang\types\Integer(6100)));
  }

  #[@test]
  public function marshal_short_wrapper_object() {
    $this->assertEquals(610, $this->fixture->marshal(new \lang\types\Short(610)));
  }

  #[@test]
  public function marshal_byte_wrapper_object() {
    $this->assertEquals(61, $this->fixture->marshal(new \lang\types\Byte(61)));
  }

  #[@test]
  public function marshal_double_wrapper_object() {
    $this->assertEquals(1.5, $this->fixture->marshal(new \lang\types\Double(1.5)));
  }

  #[@test]
  public function marshal_float_wrapper_object() {
    $this->assertEquals(1.5, $this->fixture->marshal(new \lang\types\Float(1.5)));
  }

  #[@test]
  public function marshal_bool_wrapper_object_true() {
    $this->assertEquals(true, $this->fixture->marshal(\lang\types\Boolean::$TRUE));
  }

  #[@test]
  public function marshal_bool_wrapper_object_false() {
    $this->assertEquals(false, $this->fixture->marshal(\lang\types\Boolean::$FALSE));
  }

  #[@test]
  public function marshal_traversable_array() {
    $this->assertEquals(['Hello', 'World'], iterator_to_array($this->fixture->marshal(new \lang\types\ArrayList('Hello', 'World'))));
  }

  #[@test]
  public function marshal_traversable_map() {
    $this->assertEquals(['Hello' => 'World'], iterator_to_array($this->fixture->marshal(new \lang\types\ArrayMap(['Hello' => 'World']))));
  }


  #[@test]
  public function unmarshal_string_wrapper() {
    $this->assertEquals(
      new \lang\types\String('Hello'),
      $this->fixture->unmarshal(Primitive::$STRING->wrapperClass(), 'Hello')
    );
  }

  #[@test]
  public function unmarshal_integer_wrapper() {
    $this->assertEquals(
      new \lang\types\Integer(5),
      $this->fixture->unmarshal(Primitive::$INT->wrapperClass(), 5)
    );
  }

  #[@test]
  public function unmarshal_double_wrapper() {
    $this->assertEquals(
      new \lang\types\Double(5.0),
      $this->fixture->unmarshal(Primitive::$DOUBLE->wrapperClass(), 5.0)
    );
  }

  #[@test]
  public function unmarshal_bool_wrapper() {
    $this->assertEquals(
      new \lang\types\Boolean(true),
      $this->fixture->unmarshal(Primitive::$BOOL->wrapperClass(), true)
    );
  }
}
