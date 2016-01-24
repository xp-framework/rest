<?php namespace webservices\rest;

use xml\Tree;
use xml\Node;

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

  protected function node($name, $in) {
    if (is_object($in)) {
      return Node::fromObject($in, $name);
    } else if (is_array($in)) {
      return Node::fromArray($in, $name);
    } else {
      return new Node($name, $in);
    }
  }
  
  /**
   * Serialize
   *
   * @param   var $payload
   * @param   io.streams.OutputStream $out
   * @return  void
   */
  public function serialize($payload, $out) {
    $t= new Tree();
    $t->setEncoding('UTF-8');

    if ($payload instanceof Payload) {
      $root= isset($payload->properties['name']) ? $payload->properties['name'] : 'root';
      $val= $payload->value;
    } else {
      $root= 'root';
      $val= $payload;
    }

    $out->write($t->getDeclaration()."\n");
    if ($val instanceof \Traversable) {      
      $i= 0;
      $map= null;
      foreach ($val as $key => $element) {
        if (0 === $i++) {
          $out->write('<'.$root.'>');
          $map= 0 !== $key;
        }

        $out->write($this->node($map ? $key : $root, $element)->getSource(INDENT_NONE, 'utf-8'));
      }
      if (null === $map) {
        $out->write('<'.$root.'/>');
      } else {
        $out->write('</'.$root.'>');
      }
    } else {
      $out->write($t->withRoot($this->node($root, $val))->getSource(INDENT_NONE));
    }
  }
}
