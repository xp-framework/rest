<?php namespace webservices\rest;

/**
 * Abstract base class for deserialization. Deserializers are responsible 
 * for reading the input format representation such as XML or JSON from a 
 * given stream and creating a payload from it.
 *
 * @see   xp://webservices.rest.RestJsonDeserializer
 * @see   xp://webservices.rest.RestXmlDeserializer
 * @see   xp://webservices.rest.RestFormDeserializer
 */
abstract class RestDeserializer {

  /**
   * Deserialize
   *
   * @param   io.streams.InputStream in
   * @return  var
   * @throws  lang.FormatException
   */
  public abstract function deserialize($in);
}
