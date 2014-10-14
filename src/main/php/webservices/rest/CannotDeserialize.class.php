<?php namespace webservices\rest;

use lang\FormatException;

/**
 * Indicates a certain type cannot be deserialized
 */
class CannotDeserialize extends RestDeserializer {
  protected $contentType;

  /** @param  string $contentType */
  public function __construct($contentType) { $this->contentType= $contentType; }

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
}
