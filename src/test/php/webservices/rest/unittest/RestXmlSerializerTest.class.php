<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestXmlSerializer;
use util\Date;
use util\TimeZone;
use lang\types\ArrayList;
use lang\types\ArrayMap;
use io\streams\MemoryOutputStream;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestXmlSerializer
 */
class RestXmlSerializerTest extends TestCase {

  /**
   * Serialization helper
   *
   * @param  var $value
   * @return string
   */
  protected function serialize($value) {
    $out= new MemoryOutputStream();
    (new RestXmlSerializer())->serialize($value, $out);
    return $out->getBytes();
  }

  /**
   * Assertion helper
   *
   * @param   string $expected
   * @param   string $actual
   * @throws  unittest.AssertionFailedError
   */
  protected function assertXmlEquals($expected, $actual) {
    $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>'."\n".$expected, $actual);
  }

  #[@test]
  public function null() {
    $this->assertXmlEquals(
      '<root></root>',
      $this->serialize(null)
    );
  }

  #[@test, @values(['', 'Test'])]
  public function strings($str) {
    $this->assertXmlEquals(
      '<root>'.$str.'</root>',
      $this->serialize($str)
    );
  }

  #[@test, @values([-1, 0, 1, 4711])]
  public function integers($int) {
    $this->assertXmlEquals(
      '<root>'.$int.'</root>',
      $this->serialize($int)
    );
  }

  #[@test, @values([-1.0, 0.0, 1.0, 47.11])]
  public function decimals($decimal) {
    $this->assertXmlEquals(
      '<root>'.$decimal.'</root>',
      $this->serialize($decimal)
    );
  }

  #[@test, @values([false, true])]
  public function booleans($bool) {
    $this->assertXmlEquals(
      '<root>'.$bool.'</root>',
      $this->serialize($bool)
    );
  }

  #[@test]
  public function empty_array() {
    $this->assertXmlEquals(
      '<root></root>',
      $this->serialize([])
    );
  }

  #[@test]
  public function int_array() {
    $this->assertXmlEquals(
      '<root><root>1</root><root>2</root><root>3</root></root>',
      $this->serialize([1, 2, 3])
    );
  }

  #[@test]
  public function string_array() {
    $this->assertXmlEquals(
      '<root><root>a</root><root>b</root><root>c</root></root>',
      $this->serialize(['a', 'b', 'c'])
    );
  }

  #[@test]
  public function string_map() {
    $this->assertXmlEquals(
      '<root><a>One</a><b>Two</b><c>Three</c></root>',
      $this->serialize(['a' => 'One', 'b' => 'Two', 'c' => 'Three'])
    );
  }

  #[@test]
  public function date() {
    $date= new Date('2012-12-31 18:00:00', new TimeZone('Europe/Berlin'));

    // XP6: util.Date extends Object, XP7: implements Value
    if ($date instanceof \lang\Object) {
      $this->assertXmlEquals(
        '<root><value>2012-12-31 18:00:00+0100</value><__id></__id></root>',
        $this->serialize($date)
      );
    } else {
      $this->assertXmlEquals(
        '<root><value>2012-12-31 18:00:00+0100</value></root>',
        $this->serialize($date)
      );
    }
  }

  #[@test, @values([
  #  [new \ArrayIterator([1, 2, 3])],
  #  [new ArrayList(1, 2, 3)]
  #])]
  public function traversable_array($in) {
    $this->assertXmlEquals(
      '<root><root>1</root><root>2</root><root>3</root></root>',
      $this->serialize($in)
    );
  }

  #[@test, @values([
  #  [new \ArrayIterator(['color' => 'green', 'price' => '$12.99'])],
  #  [new ArrayMap(['color' => 'green', 'price' => '$12.99'])]
  #])]
  public function traversable_map($in) {
    $this->assertXmlEquals(
      '<root><color>green</color><price>$12.99</price></root>',
      $this->serialize($in)
    );
  }

  #[@test, @values([
  #  [new \ArrayIterator([])],
  #  [new ArrayList()],
  #  [new ArrayMap([])]
  #])]
  public function empty_traversable($in) {
    $this->assertXmlEquals(
      '<root/>',
      $this->serialize($in)
    );
  }
}
