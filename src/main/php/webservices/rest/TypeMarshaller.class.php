<?php namespace webservices\rest;

/**
 * A type marshaller's responsibility is to convert objects to the
 * basic types - strings, integers, doubles, booleans, null, and
 * arrays and hashes thereof; and create objects back from these
 * in the other direction.
 *
 * Both marshal() and unmarshal() are guaranteed to be passed a
 * marshalling instance in order to be able to work on nested data
 * efficiently. Note: These are not declared here, and must be
 * declared optional in implementing instances in order to support
 * both versions of the method signature!
 */
interface TypeMarshaller {

  /**
   * Marshals the type
   *
   * @param  var type
   * @param  webservices.rest.RestMarshalling marshalling
   * @return var
   */
  public function marshal($t);

  /**
   * Unmarshals input
   *
   * @param  lang.Type target
   * @param  var in
   * @param  webservices.rest.RestMarshalling marshalling
   * @return var
   */
  public function unmarshal(\lang\Type $target, $in);
}
