<?php namespace webservices\rest\unittest;

use webservices\rest\RestFormDeserializer;
use lang\Type;
use lang\FormatException;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestJsonDeserializer
 */
class RestFormDeserializerTest extends RestDeserializerTest {

  /**
   * Creates and returns a new fixture
   *
   * @return  webservices.rest.RestDeserializer
   */
  protected function newFixture() {
    return new RestFormDeserializer();
  }

  #[@test]
  public function one_keyvalue_pair() {
    $this->assertEquals(
      ['name' => 'Timm'], 
      $this->fixture->deserialize($this->input('name=Timm'), Type::forName('[:string]'))
    );
  }

  #[@test]
  public function two_keyvalue_pairs() {
    $this->assertEquals(
      ['name' => 'Timm', 'id' => '1549'], 
      $this->fixture->deserialize($this->input('name=Timm&id=1549'), Type::forName('[:string]'))
    );
  }

  #[@test]
  public function array_of_strings() {
    $this->assertEquals(
      ['name' => ['Timm', 'Alex']], 
      $this->fixture->deserialize($this->input('name[]=Timm&name[]=Alex'), Type::forName('[:string[]]'))
    );
  }

  #[@test]
  public function map_of_strings() {
    $this->assertEquals(
      ['name' => ['thekid' => 'Timm', 'kiesel' => 'Alex']], 
      $this->fixture->deserialize($this->input('name[thekid]=Timm&name[kiesel]=Alex'), Type::forName('[:[:string]]'))
    );
  }

  #[@test]
  public function urlencoded_key() {
    $this->assertEquals(
      ['The Name' => 'Timm'], 
      $this->fixture->deserialize($this->input('The%20Name=Timm'), Type::forName('[:string]'))
    );
  }

  #[@test]
  public function urlencoded_value() {
    $this->assertEquals(
      ['name' => 'Timm Friebe'], 
      $this->fixture->deserialize($this->input('name=Timm%20Friebe'), Type::forName('[:string]'))
    );
  }

  #[@test, @expect(FormatException::class)]
  public function unbalanced_opening_bracket_in_key() {
    $this->fixture->deserialize($this->input('name[=...'), Type::$VAR);
  }

  #[@test, @expect(FormatException::class)]
  public function unbalanced_closing_bracket_in_key() {
    $this->fixture->deserialize($this->input('name]=...'), Type::$VAR);
  }
}
