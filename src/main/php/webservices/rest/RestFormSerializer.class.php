<?php namespace webservices\rest;

/**
 * An `x-www-form-urlencoded` serializer
 *
 * @see   xp://webservices.rest.RestSerializer
 */
class RestFormSerializer extends RestSerializer {

  /**
   * Return the Content-Type header's value
   *
   * @return  string
   */
  public function contentType() {
    return 'application/x-www-form-urlencoded; charset=utf-8';
  }

  /**
   * Serialize
   *
   * @param   var $payload
   * @param   io.streams.OutputStream $out
   * @return  void
   */
  public function serialize($payload, $out) {
    $sep= '';
    foreach ($payload as $key => $value) {
      $out->write($sep.$key.'='.urlencode($value));
      if ('' === $sep) $sep= '&';
    }
  }
}
