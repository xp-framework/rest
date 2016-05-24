<?php namespace webservices\rest;

use text\json\StreamInput;

/**
 * A JSON deserializer
 *
 * @see   xp://webservices.rest.RestDeserializer
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestJsonDeserializerTest
 */
class RestJsonDeserializer extends RestDeserializer {

  /**
   * Deserialize
   *
   * @param   io.streams.InputStream in
   * @return  var
   * @throws  lang.FormatException
   */
  public function deserialize($in) {
    return (new StreamInput($in))->read();
  }
}
