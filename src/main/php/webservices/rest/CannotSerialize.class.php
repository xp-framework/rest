<?php namespace webservices\rest;

use lang\FormatException;

/**
 * Indicates a certain type cannot be serialized
 */
class CannotSerialize extends RestSerializer {
  protected $contentType;

  /** @param  string $contentType */
  public function __construct($contentType) { $this->contentType= $contentType; }

  /** @return string */
  public function contentType() { return $this->contentType; }
  
  /**
   * Deserialize
   *
   * @param   var $value
   * @param   io.streams.OutputStream $out
   * @return  void
   */
  public function serialize($payload, $out) {
    throw new FormatException('Cannot serialize '.$this->contentType);
  }

  /**
   * Checks for equality
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $this->contentType === $cmp->contentType;
  }
}
