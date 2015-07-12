<?php namespace webservices\rest;

use xml\Tree;
use xml\parser\XMLParser;
use xml\parser\StreamInputSource;


/**
 * An XML deserializer
 *
 * @see   xp://webservices.rest.RestDeserializer
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestXmlDeserializerTest
 */
class RestXmlDeserializer extends RestDeserializer {

  /**
   * Deserialize
   *
   * @param   io.streams.InputStream in
   * @return  var
   * @throws  lang.FormatException
   */
  public function deserialize($in) {
    $tree= new Tree();
    (new XMLParser())->withCallback($tree)->parse(new StreamInputSource($in));
    return new RestXmlMap($tree->root);
  }
}
