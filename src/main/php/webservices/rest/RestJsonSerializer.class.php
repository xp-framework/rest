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
      $i= 0;
      $map= null;
      foreach ($val as $key => $element) {
        if (0 === $i++) {
          $map= 0 !== $key;
          $out->write($map ? '{ ' : '[ ');
        } else {
          $out->write(' , ');
        }

        if ($map) {
          $this->json->encodeTo($key, $out);
          $out->write(' : ');
        }
        $this->json->encodeTo($element, $out);
      }
      if (null === $map) {
        $out->write('[ ]');
      } else {
        $out->write($map ? ' }' : ' ]');
      }
    } else {
      $this->json->encodeTo($val, $out);
    }
  }
}
