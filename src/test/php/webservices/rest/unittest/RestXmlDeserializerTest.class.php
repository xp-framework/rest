<?php namespace webservices\rest\unittest;

use webservices\rest\RestXmlDeserializer;
use webservices\rest\RestXmlMap;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestJsonDeserializer
 */
class RestXmlDeserializerTest extends RestDeserializerTest {

  /** @return webservices.rest.RestDeserializer */
  protected function newFixture() { return new RestXmlDeserializer(); }

  /**
   * Flattens a RestXmlMap into a map
   *
   * @param  webservices.rest.RestXmlMap $in
   * @return [:var]
   */
  protected function flatten(RestXmlMap $in) {
    $result= [];
    foreach ($in as $key => $value) {
      $result[$key]= $value;
    }
    return $result;
  }

  #[@test]
  public function one_keyvalue_pair() {
    $this->assertEquals(
      ['name' => 'Timm'], 
      $this->flatten($this->fixture->deserialize($this->input('<root><name>Timm</name></root>')))
    );
  }

  #[@test]
  public function two_keyvalue_pairs() {
    $this->assertEquals(
      ['name' => 'Timm', 'id' => '1549'], 
      $this->flatten($this->fixture->deserialize($this->input('<root><name>Timm</name><id>1549</id></root>')))
    );
  }
}
