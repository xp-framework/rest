<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\Payload;

/**
 * Test payload class
 *
 * @see  xp://webservices.rest.Payload
 */
class PayloadTest extends TestCase {

  #[@test]
  public function create() {
    new Payload();
  }

  #[@test]
  public function value() {
    $value= ['key' => 'value'];
    $this->assertEquals($value, (new Payload($value))->value);
  }

  #[@test]
  public function properties() {
    $properties= ['key' => 'value'];
    $this->assertEquals($properties, (new Payload(null, $properties))->properties);
  }

  #[@test]
  public function null_payloads_are_equal() {
    $this->assertEquals(new Payload(null), new Payload(null));
  }

  #[@test]
  public function null_and_object_payloads_are_equal() {
    $this->assertNotEquals(new Payload($this), new Payload(null));
  }

  #[@test]
  public function object_payloads_are_equal() {
    $this->assertEquals(new Payload($this), new Payload($this));
  }

  #[@test]
  public function array_of_object_payloads_are_equal() {
    $this->assertEquals(new Payload([$this]), new Payload([$this]));
  }

  #[@test]
  public function identical_object_payloads_are_equal() {
    $this->assertEquals(new Payload($this), new Payload($this));
  }

  #[@test]
  public function different_object_payloads_are_not_equal() {
    $this->assertNotEquals(new Payload($this), new Payload(new \lang\Object()));
  }

  #[@test]
  public function primitive_payloads_are_equal() {
    $this->assertEquals(new Payload('test'), new Payload('test'));
  }

  #[@test]
  public function different_primitive_payloads_are_not_equal() {
    $this->assertNotEquals(new Payload('test1'), new Payload('test2'));
  }

  #[@test]
  public function map_payloads_are_equal() {
    $this->assertEquals(new Payload(['key' => 'value']), new Payload(['key' => 'value']));
  }

  #[@test]
  public function different_map_payloads_are_not_equal() {
    $this->assertNotEquals(new Payload(['key' => 'value']), new Payload(['test' => 'yes']));
  }

  #[@test]
  public function properties_are_equal() {
    $this->assertEquals(
      new Payload(null, ['key' => 'value']),
      new Payload(null, ['key' => 'value'])
    );
  }

  #[@test]
  public function different_properties_are_not_equal() {
    $this->assertNotEquals(
      new Payload(null, ['key' => 'value']),
      new Payload(null, ['test' => 'yes'])
    );
  }
}
