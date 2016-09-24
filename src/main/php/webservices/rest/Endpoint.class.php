<?php namespace webservices\rest;

use util\log\Traceable;
use peer\http\HttpConnection;
use peer\http\HttpRequest;
use lang\IllegalStateException;
use lang\IllegalArgumentException;
use peer\URL;

/**
 * REST endpoint serves as the entrypoint class for all requests
 *
 * @test xp://webservices.rest.unittest.EndpointTest
 * @test xp://webservices.rest.unittest.RoundtripTest
 * @test xp://webservices.rest.unittest.ExecutionTest
 */
class Endpoint extends \lang\Object implements Traceable {
  private $base;
  private $connectionTo;
  private $marshalling;
  private $cat= null;
  private $serializers= [];
  private $deserializers= [];
  private $timeouts= ['read' => 60.0, 'connect' => 2.0];
  private $headers= [];
  private $connections= [];

  /**
   * Creates a new Restconnection instance
   *
   * @param  peer.URL|string $base default NULL
   */
  public function __construct($base= null) {
    $this->base= $base instanceof URL ? $base : new URL((string)$base);
    $this->connectionTo= cast(['peer.http.HttpConnection', 'new'], 'function(var): var');
    $this->marshalling= new RestMarshalling();
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
   * @return void
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
   * Sets timeouts
   *
   * @param  float $read
   * @param  float $connect
   * @return self
   */
  public function usingTimeouts($read, $connect) {
    $this->timeouts= ['read' => $read, 'connect' => $connect];
    return $this;
  }

  /**
   * Sets deserializer
   *
   * @param  string $mediaType e.g. "text/xml"
   * @param  webservices.rest.Serializer $serializer
   * @return self
   */
  public function usingSerializer($mediaType, $serializer) {
    $this->serializers[$mediaType]= $serializer;
    return $this;
  }

  /**
   * Sets deserializer
   *
   * @param  string $mediaType e.g. "text/xml"
   * @param  webservices.rest.Deserializer $deserializer
   * @return self
   */
  public function usingDeserializer($mediaType, $deserializer) {
    $this->deserializers[$mediaType]= $deserializer;
    return $this;
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

  /** @return peer.URL */
  public function baseUrl() { return $this->base; }

  /** @return [:float] */
  public function timeouts() { return $this->timeouts; }

  /** @return [:string] */
  public function headers() { return $this->headers; }

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
    $host= $url->getHost();
    if (!isset($this->connections[$host])) {
      $this->connections[$host]= $this->connectionTo->__invoke($url);
      $this->connections[$host]->setConnectTimeout($this->timeouts['connect']);
      $this->connections[$host]->setTimeout($this->timeouts['read']);
    }

    $send= $this->connections[$host]->create(new HttpRequest());
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
      $response= $this->connections[$host]->send($send);
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
      '%s(->%s, timeouts: [read= %.2f, connect= %.2f])@%s',
      nameof($this),
      $this->base ? $this->base->toString() : '(null)',
      $this->timeouts['read'],
      $this->timeouts['connect'],
      $this->headers ? \xp::stringOf($this->headers) : '[]'
    );
  }
}
