<?php namespace webservices\rest\srv;

use webservices\rest\RestFormat;
use webservices\rest\RestDeserializer;

/**
 * Reads request parameters
 */
abstract class ParamReader extends \lang\Enum {
  protected static $sources= array();
  public static $COOKIE, $HEADER, $PARAM, $PATH, $BODY;

  static function __static() {
    self::$sources['cookie']= self::$COOKIE= newinstance(__CLASS__, array(1, 'cookie'), '{
      static function __static() { }
      public function read($name, $target, $request) {
        if (null === ($cookie= $request->getCookie($name, null))) return null;
        return $cookie->getValue();
      }
    }');
    self::$sources['header']= self::$HEADER= newinstance(__CLASS__, array(2, 'header'), '{
      static function __static() { }
      public function read($name, $target, $request) {
        return $request->getHeader($name, null);
      }
    }');
    self::$sources['param']= self::$PARAM= newinstance(__CLASS__, array(3, 'param'), '{
      static function __static() { }
      public function read($name, $target, $request) {
        return $request->getParam($name, null);
      }
    }');
    self::$sources['path']= self::$PATH= newinstance(__CLASS__, array(4, 'path'), '{
      static function __static() { }
      public function read($name, $target, $request) {
        return isset($target["segments"][$name]) ? rawurldecode($target["segments"][$name]) : null;
      }
    }');
    self::$sources['body']= self::$BODY= newinstance(__CLASS__, array(5, 'body'), '{
      static function __static() { }
      public function read($name, $target, $request) {
        return \webservices\rest\RestFormat::forMediaType($target["input"])->read($request->getInputStream(), \lang\Type::$VAR); 
      }
    }');
  }

  /**
   * Factory method
   *
   * @param  string name
   * @return self
   */
  public static function forName($name) {
    if (isset(self::$sources[$name])) {
      return self::$sources[$name];
    }
    throw new \lang\IllegalArgumentException('Invalid parameter source "'.$name.'"');
  }

  /**
   * Read this parameter from the given request
   *
   * @param   string name
   * @param   [:var] target Routing target
   * @param   scriptlet.Request request
   */
  public abstract function read($name, $target, $request);
}
