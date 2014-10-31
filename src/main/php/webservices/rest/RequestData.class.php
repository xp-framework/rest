<?php namespace webservices\rest;

use io\streams\MemoryOutputStream;

/**
 * Use RequestData to pass request data directly to body
 *
 * @see   xp://peer.http.RequestData
 */
class RequestData extends \peer\http\RequestData {
  protected $payload, $serializer;

  /**
   * Constructor
   *
   * @param var $payload
   * @param webservices.rest.RestSerializer $deserializer
   */
  public function __construct($payload, RestSerializer $serializer) {
    $this->payload= $payload;
    $this->serializer= $serializer;
  }

  /**
   * Retrieve data
   *
   * @return  string
   */
  public function getData() {
    $s= new MemoryOutputStream();
    $this->serializer->serialize($this->payload, $s);
    return $s->getBytes();
  }
}
