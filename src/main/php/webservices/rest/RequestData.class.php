<?php namespace webservices\rest;

use io\streams\MemoryOutputStream;

/**
 * Use RequestData to pass request data directly to body
 *
 * @see   xp://peer.http.RequestData
 */
class RequestData extends \peer\http\RequestData {

  /**
   * Constructor
   *
   * @param var $payload
   * @param webservices.rest.RestSerializer $deserializer
   */
  public function __construct($payload, RestSerializer $serializer) {
    $s= new MemoryOutputStream();
    $serializer->serialize($payload, $s);
    parent::__construct($s->getBytes(), $serializer->contentType());
  }
}
