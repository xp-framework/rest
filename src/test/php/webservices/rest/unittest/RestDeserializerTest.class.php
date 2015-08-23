<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestJsonDeserializer;
use io\streams\MemoryInputStream;
use lang\FormatException;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestDeserializer
 */
abstract class RestDeserializerTest extends TestCase {
  protected $fixture= null;

  /**
   * Sets up test case
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= $this->newFixture();
  }

  /**
   * Creates and returns a new fixture
   *
   * @return  webservices.rest.RestDeserializer
   */
  protected abstract function newFixture();

  /**
   * Creates an input stream
   *
   * @param   string bytes
   * @return  io.streams.MemoryInputStream
   */
  protected function input($bytes) {
    return new MemoryInputStream($bytes);
  }

  #[@test, @expect(FormatException::class)]
  public function empty_content() {
    $this->fixture->deserialize($this->input(''), \lang\Type::$VAR);
  }
}
