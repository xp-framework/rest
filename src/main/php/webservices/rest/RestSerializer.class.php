<?php namespace webservices\rest;



/**
 * Abstract base class for serialization. Serializers are responsible for
 * creating the output format representation such as XML or JSON from a 
 * given payload.
 *
 * @see   xp://webservices.rest.RestJsonSerializer
 * @see   xp://webservices.rest.RestXmlSerializer
 */
abstract class RestSerializer extends \lang\Object {

  /**
   * Return the Content-Type header's value
   *
   * @return  string
   */
  public abstract function contentType();
  
  /**
   * Serialize
   *
   * @param   var value
   * @param   io.streams.OutputStream $out
   * @return  void
   */
  public abstract function serialize($payload, $out);
  
}
