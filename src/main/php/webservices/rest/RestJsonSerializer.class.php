<?php namespace webservices\rest;

use text\json\StreamOutput;
use text\json\Format as WireFormat;

/**
 * A JSON serializer
 *
 * @see   xp://webservices.rest.RestSerializer
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestJsonSerializerTest
 */
class RestJsonSerializer extends RestSerializer {
  private $format;

  /**
   * Constructor. Initializes decoder member
   */
  public function __construct() {
    $this->format= WireFormat::dense();
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
    $json= new StreamOutput($out, $this->format);
    if ($val instanceof \Traversable) {
      $i= 0;
      $map= null;
      foreach ($val as $key => $element) {
        if (0 === $i++) {
          $map= 0 !== $key;
          $json->appendToken($map ? '{' : '[');
        } else {
          $json->appendToken(',');
        }

        if ($map) {
          $json->write($key);
          $json->appendToken(':');
        }
        $json->write($element);
      }
      if (null === $map) {
        $json->appendToken('[]');
      } else {
        $out->write($map ? '}' : ']');
      }
    } else {
      $json->write($val);
    }
    $json->close();
  }
}
