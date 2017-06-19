<?php namespace webservices\rest;

/**
 * A Response reader
 *
 * @test   xp://webservices.rest.unittest.ResponseReaderTest
 */
class ResponseReader {
  protected $deserializer;
  protected $marshalling;

  /**
   * Creates a new instance
   *
   * @param webservices.rest.RestDeserializer $deserializer
   * @param webservices.rest.RestMarshalling $marshalling
   */
  public function __construct(RestDeserializer $deserializer, RestMarshalling $marshalling) {
    $this->deserializer= $deserializer;
    $this->marshalling= $marshalling;
  }

  /**
   * Reads the value
   *
   * @param  lang.Type $t
   * @param  io.streams.InputStream $is
   * @return var
   */
  public function read(\lang\Type $t, \io\streams\InputStream $is) {
    return $this->marshalling->unmarshal($t, $this->deserializer->deserialize($is));
  }
}
