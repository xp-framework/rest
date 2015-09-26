<?php namespace webservices\rest\unittest;

use webservices\rest\RestJsonDeserializer;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestJsonDeserializer
 */
class RestJsonDeserializerTest extends RestDeserializerTest {

  /**
   * Creates and returns a new fixture
   *
   * @return  webservices.rest.RestDeserializer
   */
  protected function newFixture() {
    return new RestJsonDeserializer();
  }

  #[@test]
  public function one_keyvalue_pair() {
    $this->assertEquals(
      ['name' => 'Timm'], 
      $this->fixture->deserialize($this->input('{ "name" : "Timm" }'))
    );
  }

  #[@test]
  public function two_keyvalue_pairs() {
    $this->assertEquals(
      ['name' => 'Timm', 'id' => '1549'], 
      $this->fixture->deserialize($this->input('{ "name" : "Timm", "id" : "1549" }'))
    );
  }
}
