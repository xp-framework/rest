<?php namespace webservices\rest\srv;

use scriptlet\Cookie;
use util\Objects;
use lang\Value;

/**
 * Represents output
 */
abstract class Output implements Value {
  public $status;
  public $headers= [];
  public $cookies= [];

  /**
   * Sets status and returns this instance
   *
   * @param   int $status
   * @return  self
   */
  public function withStatus($status) {
    $this->status= $status;
    return $this;
  }

  /**
   * Adds a header and returns this instance
   *
   * @param   string $name
   * @param   string $value
   * @return  self
   */
  public function withHeader($name, $value) {
    $this->headers[$name]= $value;
    return $this;
  }

  /**
   * Adds a cookie and returns this instance
   *
   * @param   scriptlet.Cookie $cookie
   * @return  self
   */
  public function withCookie(Cookie $cookie) {
    $this->cookies[]= $cookie;
    return $this;
  }

  /**
   * Write response headers
   *
   * @param  scriptlet.Response $response
   * @param  peer.URL $base
   * @param  string $format
   */
  protected abstract function writeHead($response, $base, $format);

  /**
   * Write response body
   *
   * @param  scriptlet.Response $response
   * @param  peer.URL $base
   * @param  string $format
   */
  protected abstract function writeBody($response, $base, $format);

  /**
   * Write this output to the scriptlet's response
   *
   * @param  scriptlet.Response $response
   * @param  peer.URL $base
   * @param  string $format
   * @return bool handled
   */
  public function writeTo($response, $base, $format) {
    $response->setStatus($this->status);
    $this->writeHead($response, $base, $format);

    // Headers
    foreach ($this->headers as $name => $value) {
      if ('Location' === $name && false === strpos($value, '://')) {
        $url= clone $base;
        $response->setHeader($name, $url->setPath($value)->getURL());
      } else {
        $response->setHeader($name, $value);
      }
    }
    foreach ($this->cookies as $cookie) {
      $response->setCookie($cookie);
    }

    $this->writeBody($response, $base, $format);
    return true;
  }

  /** @return string */
  public function hashCode() {
    return 'O'.Objects::hashOf([$this->status, $this->headers, $this->cookies]);
  }

  /** @return string */
  public function toString() {
    $s= nameof($this).'(status= '.$this->status.")@{\n";
    foreach ($this->headers as $name => $value) {
      $s.= '  '.$name.': '.$value."\n";
    }
    foreach ($this->cookies as $cookie) {
      $s.= '  Cookie: '.$cookie->toString()."\n";
    }
    return $s.'}';
  }

  /**
   * Compares this output to a given value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self
      ? Objects::compare(
        [$this->status, $this->headers, $this->cookies],
        [$value->status, $value->headers, $value->cookies]
      )
      : 1
    ;
  }
}
