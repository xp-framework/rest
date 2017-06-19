<?php namespace webservices\rest;

class RestResource {
  private $client, $request;

  /**
   * Creates a resource
   *
   * @see    xp://webservices.rest.RestClient#resource
   * @param  webservices.rest.RestClient $client
   * @param  string $path
   * @param  [:string] $segments
   */
  public function __construct($client, $path, $segments) {
    $this->client= $client;
    $this->request= new RestRequest($path, 'GET');
    foreach ($segments as $name => $value) {
      $this->request->addSegment($name, $value);
    }
  }

  /**
   * Adds an expected media type
   *
   * @param  string $mediaType
   * @param  string $q
   * @return self
   */
  public function accepting($mediaType, $q= null) {
    $this->request->addAccept($mediaType, $q);
    return $this;
  }

  /**
   * Adds headers
   *
   * @param  [:string] $headers
   * @return self
   */
  public function with($headers) {
    foreach ($headers as $header => $value) {
      $this->request->addHeader($header, $value);
    }
    return $this;
  }

  /**
   * Adds cookies
   *
   * @param  [:string] $cookies
   * @return self
   */
  public function pass($cookies) {
    foreach ($cookies as $cookie => $value) {
      $this->request->addCookie($cookie, $value);
    }
    return $this;
  }

  /**
   * Uses a given media type for payloads
   *
   * @param  string $mediaType
   * @return self
   */
  public function using($mediaType) {
    $this->contentType= $mediaType;
    return $this;
  }

  /**
   * Issues a GET request
   *
   * @param  [:string] $params
   * @return webservices.rest.RestResponse
   */
  public function get($params= []) {
    $request= clone $this->request;
    foreach ($params as $name => $value) {
      $request->addParameter($name, $value);
    }
    return $this->client->execute($request->withMethod('GET'));
  }

  /**
   * Issues a HEAD request
   *
   * @param  [:string] $params
   * @return webservices.rest.RestResponse
   */
  public function head($params= []) {
    $request= clone $this->request;
    foreach ($params as $name => $value) {
      $request->addParameter($name, $value);
    }
    return $this->client->execute($request->withMethod('HEAD'));
  }

  /**
   * Issues a POST request
   *
   * @param  var $payload
   * @param  string $mediaType
   * @return webservices.rest.RestResponse
   */
  public function post($payload, $mediaType= null) {
    $request= clone $this->request;
    $request->setPayload($payload, $mediaType ?: $this->contentType);
    return $this->client->execute($request->withMethod('POST'));
  }

  /**
   * Issues a PUT request
   *
   * @param  var $payload
   * @param  string $mediaType
   * @return webservices.rest.RestResponse
   */
  public function put($payload, $mediaType= null) {
    $request= clone $this->request;
    $request->setPayload($payload, $mediaType ?: $this->contentType);
    return $this->client->execute($request->withMethod('PUT'));
  }

  /**
   * Issues a PATCH request
   *
   * @param  var $payload
   * @param  string $mediaType
   * @return webservices.rest.RestResponse
   */
  public function patch($payload, $mediaType= null) {
    $request= clone $this->request;
    $request->setPayload($payload, $mediaType ?: $this->contentType);
    return $this->client->execute($request->withMethod('PATCH'));
  }

  /**
   * Issues a DELETE request
   *
   * @param  [:string] $params
   * @return webservices.rest.RestResponse
   */
  public function delete($params= []) {
    $request= clone $this->request;
    foreach ($params as $name => $value) {
      $request->addParameter($name, $value);
    }
    return $this->client->execute($request->withMethod('DELETE'));
  }
}