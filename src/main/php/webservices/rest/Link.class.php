<?php namespace webservices\rest;

use util\Objects;

/**
 * A single link inside the Links header
 *
 * @see  xp://webservices.rest.Links
 * @test xp://webservices.rest.unittest.LinksTest
 */
class Link implements \lang\Value {
  private $uri, $params;

  /**
   * Creates a new link
   *
   * @param  string $uri
   * @param  [:string] $params
   */
  public function __construct($uri, $params) {
    $this->uri= $uri;
    $this->params= $params;
  }

  /** @return string */
  public function uri() { return $this->uri; }

  /** @return [:string] */
  public function params() { return $this->params; }

  /**
   * Returns whether a given parameter is present
   *
   * @param  string $name
   * @return bool
   */
  public function present($name) {
    return array_key_exists($name, $this->params);
  }

  /**
   * Returns a given parameter's value
   *
   * @param  string $name
   * @return var
   * @throws lang.IndexOutOfBoundsException
   */
  public function param($name) {
    return $this->params[$name];
  }

  /**
   * Compares this links to a given value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    if ($value instanceof self) {
      return 0 === ($p= strcmp($this->uri, $value->uri)) ? Objects::compare($this->params, $value->params) : $p;
    }
    return 1;
  }

  /** @return string */
  public function hashCode() {
    return crc32($this->uri).Objects::hashOf($this->params);
  }

  /** @return string */
  public function toString() {
    $s= nameof($this).'<'.$this->uri.'>';
    foreach ($this->params as $param => $value) {
      $s.= '; '.$param.'="'.$value.'" ';
    }
    return substr($s, 0, -1);
  }
}