<?php namespace webservices\rest\unittest;

use webservices\rest\ResponseReader;
use webservices\rest\RestMarshalling;
use webservices\rest\CannotDeserialize;

class ResponseReaderTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new ResponseReader(new CannotDeserialize('text/plain'), new RestMarshalling());
  }
}