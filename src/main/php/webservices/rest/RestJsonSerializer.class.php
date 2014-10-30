<?php namespace webservices\rest;

use webservices\json\JsonFactory;

/**
 * A JSON serializer
 *
 * @see   xp://webservices.rest.RestSerializer
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestJsonSerializerTest
 */
class RestJsonSerializer extends RestSerializer {
  protected $json;

  /**
   * Constructor. Initializes decoder member
   */
  public function __construct() {
    $this->json= JsonFactory::create();
  }

  /**
   * Return the Content-Type header's value
   *
   * @return  string
   */
  public function contentType() {
    return 'application/json; charset=utf-8';
  }

  /**
   * Serialize
   *
   * @param   var $payload
   * @param   io.streams.OutputStream $out
   * @return  void
   */
  public function serialize($payload, $out) {
    $val= $payload instanceof Payload ? $payload->value : $payload;

    if ($val instanceof \Traversable) {
      $this->json->encodeTo(iterator_to_array($val), $out);
    } else {
      $this->json->encodeTo($val, $out);
    }
  }
}
