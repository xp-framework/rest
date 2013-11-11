<?php namespace webservices\rest\srv;

use webservices\rest\TypeMarshaller;


/**
 * Default exception mapping
 *
 * <code>
 *   { "message" : "Exception message" }
 * </code>
 */
class DefaultExceptionMarshaller extends \lang\Object implements TypeMarshaller {

  /**
   * Marshals the type
   *
   * @param  lang.Throwable type
   * @return var
   */
  public function marshal($t) {
    return array('message' => $t->getMessage());
  }

  /**
   * Unmarshals input
   *
   * @param  lang.Type target
   * @param  var in
   * @return lang.Throwable
   */
  public function unmarshal(\lang\Type $target, $in) {
    return $in instanceof \lang\Throwable ? $in : $target->newInstance((string)$in);
  }
}
