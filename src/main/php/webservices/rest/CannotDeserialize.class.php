<?php namespace webservices\rest;

use lang\FormatException;

/**
 * Indicates a certain type cannot be deserialized
 */
class CannotDeserialize extends RestDeserializer {
  protected $contentType;

  /** @param  string $contentType */
  public function __construct($contentType) { $this->contentType= $contentType; }

  /** @return string */
  public function contentType() { return $this->contentType; }

  /**
   * Deserialize
   *
   * @param   io.streams.InputStream in
   * @return  var
   * @throws  lang.FormatException
   */
  public function deserialize($in) {
    throw new FormatException('Cannot deserialize '.$this->contentType);
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
