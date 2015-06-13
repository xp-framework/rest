<?php namespace webservices\rest;

use io\streams\InputStream;
use io\streams\OutputStream;

interface Format {

  /**
   * Returns whether this format can be handled
   *
   * @return bool
   */
  public function isHandled();

  /**
   * Get serializer
   *
   * @return webservices.rest.RestSerializer
   */
  public function serializer();

  /**
   * Get deserializer
   *
   * @return webservices.rest.RestDeserializer
   */
  public function deserializer();

  /**
   * Deserialize from input
   *
   * @param  io.streams.InputStream in
   * @return var
   */
  public function read(InputStream $in);

  /**
   * Serialize and write to output
   *
   * @param  io.streams.OutputStream out
   * @param  webservices.rest.Payload value
   */
  public function write(OutputStream $out, Payload $value= null);

}