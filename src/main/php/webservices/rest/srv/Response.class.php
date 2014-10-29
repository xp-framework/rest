<?php namespace webservices\rest\srv;

use webservices\rest\Payload;
use webservices\rest\RestFormat;

/**
 * The Response class can be used to control the HTTP status code and headers
 * of a REST call.
 *
 * ```php
 * #[@webservice(verb= 'POST', path= '/resources')]
 * public function addElement(Element $element) {
 *   // TBI: Create element
 *   return Response::created();
 * }
 * ```
 *
 * @test  xp://net.xp_framework.unittest.webservices.rest.srv.ResponseTest
 */
class Response extends Output {
  public $payload;

  /**
   * Creates a new response instance
   *
   * @param  int $status
   */
  public function __construct($status= null) {
    $this->status= $status;
  }

  /**
   * Creates a new response instance with the status code set to 200 (OK)
   *
   * @return  self
   */
  public static function ok() {
    return new self(200);
  }

  /**
   * Creates a new response instance with the status code set to 201 (Created)
   * and an optional location.
   *
   * @param   string location
   * @return  self
   */
  public static function created($location= null) {
    $self= new self(201);
    if (null !== $location) $self->headers['Location']= $location;
    return $self;
  }

  /**
   * Creates a new response instance with the status code set to 204 (No content)
   *
   * @return  self
   */
  public static function noContent() {
    return new self(204);
  }

  /**
   * Creates a new response instance with the status code set to 302 (See other)
   * and a specified location.
   *
   * @param   string location
   * @return  self
   */
  public static function see($location) {
    $self= new self(302);
    $self->headers['Location']= $location;
    return $self;
  }

  /**
   * Creates a new response instance with the status code set to 304 (Not modified)
   *
   * @return  self
   */
  public static function notModified() {
    return new self(304);
  }

  /**
   * Creates a new response instance with the status code set to 404 (Not found)
   *
   * @return  self
   */
  public static function notFound() {
    return new self(404);
  }

  /**
   * Creates a new response instance with the status code set to 406 (Not acceptable)
   *
   * @return  self
   */
  public static function notAcceptable() {
    return new self(406);
  }

  /**
   * Creates a new response instance with the status code set to a given status.
   *
   * @param   int code
   * @return  self
   */
  public static function status($code) {
    return new self($code);
  }

  /**
   * Creates a new response instance with the status code optionally set to a given
   * error code (defaulting to 500 - Internal Server Error).
   *
   * @param   int code
   * @return  self
   */
  public static function error($code= 500) {
    return new self($code);
  }

  /**
   * Sets payload and returns this instance
   * 
   * @param   var data
   * @return  self
   */
  public function withPayload($data) {
    if ($data instanceof Payload) {
      $this->payload= $data;
    } else {
      $this->payload= new Payload($data);
    }
    return $this;
  }

  /**
   * Write response headers
   *
   * @param  scriptlet.Response response
   * @param  peer.URL base
   * @param  string format
   */
  protected function writeHead($response, $base, $format) {
    if (null !== $this->payload && !isset($this->headers['Content-Type'])) {
      $response->setContentType($format);
    }
  }

  /**
   * Write response body
   *
   * @param  scriptlet.Response response
   * @param  peer.URL base
   * @param  string format
   */
  protected function writeBody($response, $base, $format) {
    if (null !== $this->payload) {
      RestFormat::forMediaType($format)->write($response->getOutputStream(), $this->payload);
    }
  }

  /**
   * Returns whether a given value is equal to this Response instance
   *
   * @param  var cmp
   * @return bool
   */
  public function equals($cmp) {
    return (
      parent::equals($cmp) &&
      (null === $this->payload ? null === $cmp->payload : $this->payload->equals($cmp->payload))
    );
  }
}
