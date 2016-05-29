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
   * Constructor.
   *
   * @param  text.json.Format $format Optional wire format, defaults to *dense* format
   */
  public function __construct(WireFormat $format= null) {
    $this->format= $format ?: WireFormat::dense();
  }

  /**
   * Return the Content-Type header's value
   *
   * @return string
   */
  public function contentType() {
    return 'application/json; charset=utf-8';
  }

  /**
   * Serialize
   *
   * @param  var $payload
   * @param  io.streams.OutputStream $out
   * @return void
   */
  public function serialize($payload, $out) {
    (new StreamOutput($out, $this->format))->write($payload instanceof Payload
      ? $payload->value
      : $payload
    );
  }
}
