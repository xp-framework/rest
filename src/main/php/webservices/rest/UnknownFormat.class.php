<?php namespace webservices\rest;

use io\streams\InputStream;
use io\streams\OutputStream;

/**
 * Unknown format. Raises exceptions when reading / writing.
 */
class UnknownFormat implements Format {
  private $mediatype;

  /** @param string mediatype */
  public function __construct($mediatype) { $this->mediatype= $mediatype; }

  /** @return bool */
  public function isHandled() { return false; }

  /** @return webservices.rest.RestSerializer */
  public function serializer() { return new CannotSerialize($this->mediatype); }

  /** @return webservices.rest.RestDeserializer */
  public function deserializer() { return new CannotDeserialize($this->mediatype); }

  /**
   * Deserialize from input
   *
   * @param  io.streams.InputStream in
   * @return var
   */
  public function read(InputStream $in) {
    return $this->deserializer()->deserialize($in);
  }

  /**
   * Serialize and write to output
   *
   * @param  io.streams.OutputStream out
   * @param  webservices.rest.Payload value
   */
  public function write(OutputStream $out, Payload $value= null) {
    $this->serializer()->serialize($value, $out);
  }
}
