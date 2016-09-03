<?php namespace webservices\rest;

use util\log\Traceable;
use peer\http\HttpConnection;
use peer\http\HttpRequest;
use lang\IllegalStateException;
use lang\IllegalArgumentException;
use lang\XPClass;
use peer\URL;

/**
 * REST client
 *
 * @test xp://net.xp_framework.unittest.webservices.rest.RestClientTest
 * @test xp://net.xp_framework.unittest.webservices.rest.RestClientSendTest
 * @test xp://net.xp_framework.unittest.webservices.rest.RestClientExecutionTest
 */
class RestClient extends \lang\Object implements Traceable {
  private $connectionTo;
  private $cat= null;
  private $serializers= [];
  private $deserializers= [];
  private $marshalling= null;
  private $base= null;
  private $connectTimeout= 2.0;
  private $readTimeout= 60.0;
  private $headers= [];
  private $connections= [];

  /**
   * Creates a new Restconnection instance
   *
   * @param  peer.URL|string $base default NULL
   */
  public function __construct($base= null) {
    $this->connectionTo= function($url) { return new HttpConnection($url); };
    $this->marshalling= new RestMarshalling();
    if (null !== $base) $this->setBase($base);
  }

  /**
   * Adds headers to be sent with every request
   *
   * @param  [:string] $headers
   * @return self
   */
  public function with($headers) {
    $this->headers= $headers;
    return $this;
  }

  /**
   * Set trace
   *
   * @param  util.log.LogCategory $cat
   */
  public function setTrace($cat) {
    $this->cat= $cat;
  }

  /**
   * Sets function to create connections with
   *
   * @param  function(peer.URL): peer.http.HttpConnection $creation
   * @return self
   */
  public function usingConnections($creation) {
    $this->connectionTo= cast($creation, 'function(var): var');
    return $this;
  }

  /**
   * Sets connection
   *
   * @deprecated Use usingConnections() instead
   * @param  peer.URL|string $base either a peer.URL or a string
   */
  public function setConnection($conn) {
    $this->base= $conn->getUrl();
  }

  /**
   * Sets base
   *
   * @param  peer.URL|string $base either a peer.URL or a string
   */
  public function setBase($base) {
    $this->base= $base instanceof URL ? $base : new URL($base);
  }

  /**
   * Sets base and returns this connection
   *
   * @param  peer.URL|string $base either a peer.URL or a string
   * @return self
   */
  public function withBase($base) {
    $this->setBase($base);
    return $this;
  }

  /** @return peer.URL */
  public function getBase() { return $this->base; }

  /** @param float $timeout */
  public function setConnectTimeout($timeout) { $this->connectTimeout= $timeout; }

  /** @return float */
  public function getConnectTimeout() { return $this->connectTimeout; }

  /** @param float $timeout */
  public function setTimeout($timeout) { $this->readTimeout= $timeout; }

  /** @return float */
  public function getTimeout() { return $this->readTimeout; }

  /**
   * Sets deserializer
   *
   * @param  string $mediaType e.g. "text/xml"
   * @param  webservices.rest.Deserializer $deserializer
   */
  public function setDeserializer($mediaType, $deserializer) {
    $this->deserializers[$mediaType]= $deserializer;
  }

  /**
   * Returns a deserializer
   *
   * @param  string $contentType
   * @return webservices.rest.RestDeserializer
   */
  public function deserializerFor($contentType) {
    $mediaType= substr($contentType, 0, strcspn($contentType, ';'));
    if (isset($this->deserializers[$mediaType])) {
      return $this->deserializers[$mediaType];
    } else {
      return RestFormat::forMediaType($mediaType)->deserializer();
    }
  }

  /**
   * Sets deserializer
   *
   * @param  string $mediaType e.g. "text/xml"
   * @param  webservices.rest.Serializer $serializer
   */
  public function setSerializer($mediaType, $serializer) {
    $this->serializers[$mediaType]= $serializer;
  }

  /**
   * Returns a serializer
   *
   * @param  string $contentType
   * @return webservices.rest.RestSerializer
   */
  public function serializerFor($contentType) {
    $mediaType= substr($contentType, 0, strcspn($contentType, ';'));
    if (isset($this->serializers[$mediaType])) {
      return $this->serializers[$mediaType];
    } else {
      return RestFormat::forMediaType($mediaType)->serializer();
    }
  }

  /**
   * Returns a resource by a given path
   *
   * @param  string $path
   * @param  [:string] $segments Optional values for placeholders in path
   * @return webservices.rest.RestResource
   */
  public function resource($path, $segments= []) {
    return new RestResource($this, $path, $segments);
  }

  /**
   * Execute a request
   *
   * @param  webservices.rest.RestRequest $request
   * @return webservices.rest.RestResponse
   * @throws lang.IllegalStateException if no connection is set
   */
  public function execute(RestRequest $request) {
    $url= $request->targetUrl($this->base);
    $key= $url->getHost();
    if (!isset($this->connections[$key])) {
      $this->connections[$key]= $this->connectionTo->__invoke($url);
      $this->connections[$key]->setConnectTimeout($this->connectTimeout);
      $this->connections[$key]->setTimeout($this->readTimeout);
    }

    $send= $this->connections[$key]->create(new HttpRequest());
    $send->addHeaders($this->headers);
    $send->addHeaders($request->headerList());
    $send->setMethod($request->getMethod());
    $send->setTarget($url->getPath());

    // Compose body
    // * Serialize payloads using the serializer for the given mimetype
    // * Use bodies as-is, e.g. file uploads
    // * If no body and no payload is set, use parameters
    if ($request->hasPayload()) {
      $send->setParameters(new RequestData(
        $this->marshalling->marshal($request->getPayload()),
        $this->serializerFor($request->getContentType())
      ));
    } else if ($request->hasBody()) {
      $send->setParameters($request->getBody());
    } else {
      $send->setParameters($request->getParameters());
    }
    
    try {
      $this->cat && $this->cat->debug('>>>', $send->getRequestString());
      $response= $this->connections[$key]->send($send);
    } catch (\io\IOException $e) {
      throw new RestException('Cannot send request', $e);
    }

    $reader= new ResponseReader($this->deserializerFor($response->header('Content-Type')[0]), $this->marshalling);
    $result= new RestResponse($response, $reader);

    $this->cat && $this->cat->debug('<<<', $response->toString(), $result->contentCopy());
    return $result;
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      '%s(->%s, timeout: [read= %.2f, connect= %.2f])',
      nameof($this),
      $this->base ? $this->base->toString() : '(null)',
      $this->readTimeout,
      $this->connectTimeout
    );
  }
}
