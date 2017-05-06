<?php namespace webservices\rest;

use peer\http\HttpConstants;
use peer\http\Header;
use lang\ElementNotFoundException;
use lang\IllegalStateException;
use peer\URL;

/**
 * A REST request
 *
 * @test    xp://net.xp_framework.unittest.webservices.rest.RestRequestTest
 */
class RestRequest extends \lang\Object {
  protected $resource= '/';
  protected $method= '';
  protected $contentType= null;
  protected $parameters= [];
  protected $segments= [];
  protected $headers= [];
  protected $accept= [];
  protected $payload= null;
  protected $body= null;

  /**
   * Creates a new RestRequest instance
   *
   * @param   string $uri default '/'
   * @param   string $method default HttpConstants::GET
   */
  public function __construct($uri= '/', $method= HttpConstants::GET) {
    if ($p= strpos($uri, '?')) {
      parse_str(substr($uri, $p + 1), $this->parameters);
      $this->resource= substr($uri, 0, $p);
    } else {
      $this->resource= $uri;
    }
    $this->method= $method;
  }
  
  /**
   * Sets resource
   *
   * @param   string resource
   */
  public function setResource($resource) {
    $this->resource= $resource;
  }

  /**
   * Sets resource
   *
   * @param   string resource
   * @return  self
   */
  public function withResource($resource) {
    $this->resource= $resource;
    return $this;
  }

  /**
   * Gets resource
   *
   * @return  string resource
   */
  public function getResource() {
    return $this->resource;
  }

  /**
   * Sets method
   *
   * @param   string method
   */
  public function setMethod($method) {
    $this->method= $method;
  }

  /**
   * Sets method
   *
   * @param   string method
   * @return  self
   */
  public function withMethod($method) {
    $this->method= $method;
    return $this;
  }

  /**
   * Gets method
   *
   * @return  string method
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * Sets body
   *
   * @param   peer.http.RequestData body
   */
  public function setBody(\peer\http\RequestData $body) {
    $this->body= $body;
  }

  /**
   * Sets body
   *
   * @param   peer.http.RequestData body
   * @return  self
   */
  public function withBody(\peer\http\RequestData $body) {
    $this->body= $body;
    return $this;
  }

  /**
   * Adds an expected mime type
   *
   * @param   string range
   * @param   string q
   */
  public function addAccept($type, $q= null) {
    $range= $type;
    null === $q || $range.= ';q='.$q;
    $this->accept[]= $range;
  }

  /**
   * Adds an expected mime type
   *
   * @param   string range
   * @param   string q
   * @return  self
   */
  public function withAccept($type, $q= null) {
    $this->addAccept($type, $q);
    return $this;
  }

  /**
   * Adds a cookie
   *
   * @param   string $name cookie name
   * @param   string $value default ''
   * @return  void
   */
  public function addCookie($name, $value= '') {
    $this->headers[]= new Header('Cookie', $name.'='.$value);
  }

  /**
   * Adds a cookie
   *
   * @param   string $name cookie name
   * @param   string $value default ''
   * @return  self
   */
  public function withCookie($name, $value= '') {
    $this->addCookie($name, $value);
    return $this;
  }

  /**
   * Sets payload
   *
   * @param   var payload
   * @param   var format either a string, a RestFormat or a RestSerializer instance
   */
  public function setPayload($payload, $format) {
    $this->payload= $payload;
    if ($format instanceof RestFormat) {
      $this->contentType= $format->serializer()->contentType();
    } else if ($format instanceof RestSerializer) {
      $this->contentType= $format->contentType();
    } else {
      $this->contentType= $format;
    }
  }

  /**
   * Sets payload
   *
   * @param   var payload
   * @param   var format
   * @return  self
   */
  public function withPayload($payload, $format) {
    $this->setPayload($payload, $format);
    return $this;
  }

  /**
   * Gets payload
   *
   * @return  var
   */
  public function hasPayload() {
    return null !== $this->payload;
  }

  /**
   * Gets payload
   *
   * @return  var
   */
  public function getPayload() {
    return $this->payload;
  }

  /**
   * Gets content type
   *
   * @return  string
   */
  public function getContentType() {
    return $this->contentType;
  }

  /**
   * Gets body
   *
   * @return  peer.http.RequestData body
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * Gets whether a body is set
   *
   * @return  bool
   */
  public function hasBody() {
    return null !== $this->body;
  }

  /**
   * Adds a parameter
   *
   * @param   string name
   * @param   string value
   */
  public function addParameter($name, $value) {
    $this->parameters[$name]= $value;
  }

  /**
   * Adds a parameter
   *
   * @param   string name
   * @param   string value
   * @return  self
   */
  public function withParameter($name, $value) {
    $this->parameters[$name]= $value;
    return $this;
  }

  /**
   * Adds a segment
   *
   * @param   string name
   * @param   string value
   */
  public function addSegment($name, $value) {
    $this->segments[$name]= $value;
  }

  /**
   * Adds a segment
   *
   * @param   string name
   * @param   string value
   * @return  self
   */
  public function withSegment($name, $value) {
    $this->segments[$name]= $value;
    return $this;
  }

  /**
   * Adds a header
   *
   * @param   var arg
   * @param   string value
   * @return  peer.http.Header
   */
  public function addHeader($arg, $value= null) {
    if ($arg instanceof Header || $arg instanceof \peer\Header) {
      $h= $arg;
    } else {
      $h= new Header($arg, $value);
    }
    $this->headers[]= $h;
    return $h;
  }

  /**
   * Adds a header
   *
   * @param   var arg
   * @param   string value
   * @return  self
   */
  public function withHeader($arg, $value= null) {
    $this->addHeader($arg, $value);
    return $this;
  }

  /**
   * Returns a parameter specified by its name
   *
   * @param   string name
   * @return  string value
   * @throws  lang.ElementNotFoundException
   */
  public function getParameter($name) {
    if (!isset($this->parameters[$name])) {
      throw new ElementNotFoundException('No such parameter "'.$name.'"');
    }
    return $this->parameters[$name];
  }

  /**
   * Returns all parameters
   *
   * @return  [:string]
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * Returns a segment specified by its name
   *
   * @param   string name
   * @return  string value
   * @throws  lang.ElementNotFoundException
   */
  public function getSegment($name) {
    if (!isset($this->segments[$name])) {
      throw new ElementNotFoundException('No such segment "'.$name.'"');
    }
    return $this->segments[$name];
  }

  /**
   * Returns all segments
   *
   * @return  [:string]
   */
  public function getSegments() {
    return $this->segments;
  }

  /**
   * Returns a header specified by its name
   *
   * @param   string name
   * @return  string value
   * @throws  lang.ElementNotFoundException
   */
  public function getHeader($name) {
    if ('Content-Type' === $name) {
      return $this->contentType;
    } else if ('Accept' === $name) {
      return $this->accept;
    } else foreach ($this->headers as $header) {
      if ($name === $header->name()) return $header->value();
    }
    throw new ElementNotFoundException('No such header "'.$name.'"');
  }

  /**
   * Returns all headers
   *
   * @return  [:string]
   */
  public function getHeaders() {
    $headers= [];
    foreach ($this->headers as $header) {
      $headers[$header->name()]= $header->value();
    }
    $this->contentType && $headers['Content-Type']= $this->contentType;
    $this->accept && $headers['Accept']= implode(', ', $this->accept);
    return $headers;
  }

  /**
   * Returns all headers
   *
   * @return  peer.http.Header[]
   */
  public function headerList() {
    return array_merge(
      $this->headers,
      $this->contentType ? [new Header('Content-Type', $this->contentType)] : [],
      $this->accept ? [new Header('Accept', implode(', ', $this->accept))] : []
    );
  }

  /**
   * Resolves segments in resource
   *
   * @param  string $resource
   * @param  bool $encode
   * @return string
   */
  private function resolve($resource, $encode) {
    $l= strlen($resource);
    $target= '';
    $offset= 0;
    do {
      $b= strcspn($resource, '{', $offset);
      $target.= substr($resource, $offset, $b);
      $offset+= $b;
      if ($offset >= $l) break;
      $e= strcspn($resource, '}', $offset);
      $segment= $this->getSegment(substr($resource, $offset+ 1, $e- 1));
      $target.= $encode ? rawurlencode($segment) : $segment;
      $offset+= $e+ 1;
    } while ($offset < $l);

    return $target;
  }

  /**
   * Gets target
   *
   * @deprecated Use targetUrl() instead!
   * @param  string $base
   * @return string
   */
  public function getTarget($base= '/') {
    return $this->resolve(rtrim($base, '/').'/'.ltrim($this->resource, '/'), true);
  }

  /**
   * Copy authentication if on same host 
   *
   * @param  peer.URL $base
   * @param  peer.URL $url
   * @return peer.URL The given URL
   */
  private function authenticate($base, $url) {
    if ($base && ($url->getHost() === $base->getHost())) {
      $url->setUser($base->getUser());
      $url->setPassword($base->getPassword());
    }
    return $url;
  }

  /**
   * Returns parameters, resolving segments if necessary
   *
   * @return [:string]
   */
  public function targetParameters() {
    $return= [];
    foreach ($this->parameters as $name => $parameter) {
      $return[$name]= $this->resolve($parameter, false);
    }
    return $return;
  }

  /**
   * Resolves target URL
   *
   * @param  peer.URL $base
   * @return peer.URL
   * @throws lang.IllegalStateException if no base URL set and relative URL used in this request
   */
  public function targetUrl(URL $base= null) {
    if (strpos($this->resource, '://')) {
      $url= $this->authenticate($base, new URL($this->resource));
      $resource= $url->getPath();
    } else if (null === $base) {
      throw new IllegalStateException('No base set');
    } else if (0 === strncmp('//',  $this->resource, 2)) {
      $url= $this->authenticate($base, new URL($base->getScheme().':'.$this->resource));
      $resource= $url->getPath();
    } else if ('/' === $this->resource{0}) {
      $resource= $this->resource;
      $url= clone $base;
    } else {
      $resource= rtrim($base->getPath('/'), '/').'/'.ltrim($this->resource, '/');
      $url= clone $base;
    }

    return $url->setParams($this->targetParameters())->setPath($this->resolve($resource, true));
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    $headers= "\n";
    foreach ($this->headers as $header) {
      $headers.= '  '.$header->name().': '.\xp::stringOf($header->value())."\n";
    }
    if ($this->accept) {
      $headers.='  Accept: '.implode(', ', $this->accept)."\n";
    }

    return nameof($this).'('.$this->method.' '.$this->resource.')@['.$headers.']';
  }
}
