<?php namespace webservices\rest;

use webservices\json\JsonFactory;


/**
 * A JSON deserializer
 *
 * @see   xp://webservices.rest.RestDeserializer
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestJsonDeserializerTest
 */
class RestJsonDeserializer extends RestDeserializer {
  protected $json;

  /**
   * Constructor. Initializes decoder member
   */
  public function __construct() {
    $this->json= JsonFactory::create();
  }

  /**
   * Deserialize
   *
   * @param   io.streams.InputStream in
   * @return  var
   * @throws  lang.FormatException
   */
  public function deserialize($in) {
    try {
      return $this->json->decodeFrom($in);
    } catch (\webservices\json\JsonException $e) {
      throw new \lang\FormatException('Malformed JSON', $e);
    }
  }
}
