<?php namespace webservices\rest;



/**
 * Exception mapping
 *
 */
interface TypeMarshaller {

  /**
   * Marshals the type
   *
   * @param  T type
   * @return var
   */
  public function marshal($t);

  /**
   * Unmarshals input
   *
   * @param  lang.Type target
   * @param  var in
   * @return T
   */
  public function unmarshal(\lang\Type $target, $in);
}
