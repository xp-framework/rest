<?php namespace webservices\rest;

use xml\Tree;


/**
 * An XML serializer
 *
 * @see   xp://webservices.rest.RestSerializer
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestXmlSerializerTest
 */
class RestXmlSerializer extends RestSerializer {

  /**
   * Return the Content-Type header's value
   *
   * @return  string
   */
  public function contentType() {
    return 'text/xml; charset=utf-8';
  }
  
  /**
   * Serialize
   *
   * @param   var value
   * @return  string
   */
  public function serialize($payload) {
    $t= new Tree();
    $t->setEncoding('UTF-8');

    if ($payload instanceof \Payload) {
      $root= isset($payload->properties['name']) ? $payload->properties['name'] : 'root';
      $payload= $payload->value;
    } else {
      $root= 'root';
    }

    if ($payload instanceof \lang\Generic) {
      $t->root= \xml\Node::fromObject($payload, $root);
    } else if (is_array($payload)) {
      $t->root= \xml\Node::fromArray($payload, $root);
    } else {
      $t->root= new \xml\Node($root, $payload);
    }
    return $t->getDeclaration()."\n".$t->getSource(INDENT_NONE);
  }
}
