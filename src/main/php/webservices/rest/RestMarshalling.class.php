<?php namespace webservices\rest;

use lang\XPClass;
use lang\Type;
use lang\Primitive;
use lang\reflect\Modifiers;

/**
 * Marshalling takes care of converting the data to a simple output 
 * format consisting solely of primitives, arrays and maps; and vice
 * versa.
 *
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestMarshallingTest
 */
class RestMarshalling extends \lang\Object {
  protected $marshallers;

  /**
   * Constructor
   */
  public function __construct() {
    $this->marshallers= create('new util.collections.HashTable<lang.Type, webservices.rest.TypeMarshaller>');

    if (PHP_VERSION < '7.0.0') {
      $strings= newinstance('webservices.rest.TypeMarshaller', [], [
        'marshal'   => function($t) { return $t->toString(); },
        'unmarshal' => function(Type $target, $in) { return $target->newInstance($in); }
      ]);
      $integers= newinstance('webservices.rest.TypeMarshaller', [], [
        'marshal'   => function($t) { return $t->intValue(); },
        'unmarshal' => function(Type $target, $in) { return $target->newInstance($in); }
      ]);
      $decimals= newinstance('webservices.rest.TypeMarshaller', [], [
        'marshal'   => function($t) { return $t->doubleValue(); },
        'unmarshal' => function(Type $target, $in) { return $target->newInstance($in); }
      ]);
      $booleans= newinstance('webservices.rest.TypeMarshaller', [], [
        'marshal'   => function($t) { return (bool)$t->value; },
        'unmarshal' => function(Type $target, $in) { return $target->newInstance($in); }
      ]);

      $this->marshallers[XPClass::forName('lang.types.String')]= $strings;
      $this->marshallers[XPClass::forName('lang.types.Character')]= $strings;
      $this->marshallers[XPClass::forName('lang.types.Long')]= $integers;
      $this->marshallers[XPClass::forName('lang.types.Integer')]= $integers;
      $this->marshallers[XPClass::forName('lang.types.Short')]= $integers;
      $this->marshallers[XPClass::forName('lang.types.Byte')]= $integers;
      $this->marshallers[XPClass::forName('lang.types.Float')]= $decimals;
      $this->marshallers[XPClass::forName('lang.types.Double')]= $decimals;
      $this->marshallers[XPClass::forName('lang.types.Boolean')]= $booleans;
    }
  }

  /**
   * Adds a type marshaller
   *
   * @param  var type either a full qualified type name or a type instance
   * @param  webservices.rest.TypeMarshaller m
   * @return webservices.rest.TypeMarshaller The added marshaller
   */
  public function addMarshaller($type, TypeMarshaller $m) {
    $keys= $this->marshallers->keys();

    // Add marshaller
    $t= $type instanceof Type ? $type : Type::forName($type);
    $this->marshallers[$t]= $m;

    // Iterate over map keys before having altered the map, checking for
    // any marshallers less specific than the added marshaller, and move
    // them to the end. E.g. if a marshaller for Dates is added, it needs 
    // to be in the map *before* the one for for Objects!
    foreach ($keys as $type) {
      if ($type->isAssignableFrom($t)) {
        $this->marshallers->put($type, $this->marshallers->remove($type));
      }
    }
    return $m;
  }

  /**
   * Adds a type marshaller
   *
   * @param  var type either a full qualified type name or a type instance
   * @return webservices.rest.TypeMarshaller The added marshaller
   */
  public function getMarshaller($type) {
    return $this->marshallers[$type instanceof Type ? $type : Type::forName($type)];
  }

  /**
   * Calculate variants of a given name
   *
   * @param   string name
   * @return  string[] names
   */
  protected function variantsOf($name) {
    $variants= [$name];
    $chunks= explode('_', $name);
    if (sizeof($chunks) > 1) {      // product_id => productId
      $variants[]= array_shift($chunks).implode(array_map('ucfirst', $chunks));
    }
    return $variants;
  }

  /**
   * Convert data
   *
   * @param   var data
   * @return  var
   */
  public function marshal($data) {
    if ($data instanceof \util\Date) {
      return $data->toString('c');    // ISO 8601, e.g. "2004-02-12T15:19:21+00:00"
    } else if ($data instanceof \Traversable) {
      return new Iteration($data, [$this, 'marshal']);
    } else if ($data instanceof \lang\Generic) {
      foreach ($this->marshallers->keys() as $t) {      // Specific class marshalling
        if ($t->isInstance($data)) return $this->marshallers[$t]->marshal($data, $this);
      }

      $class= $data->getClass();
      $r= [];
      foreach ($class->getFields() as $field) {
        $m= $field->getModifiers();
        if ($m & MODIFIER_STATIC) {
          continue;
        } else if ($field->getModifiers() & MODIFIER_PUBLIC) {
          $r[$field->getName()]= $this->marshal($field->get($data));
        } else {
          foreach ($this->variantsOf($field->getName()) as $name) {
            if ($class->hasMethod($m= 'get'.$name)) {
              $r[$field->getName()]= $this->marshal($class->getMethod($m)->invoke($data));
              continue 2;
            }
          }
        }
      }
      return $r;
    } else if (is_array($data)) {
      $r= [];
      foreach ($data as $key => $val) {
        $r[$key]= $this->marshal($val);
      }
      return $r;
    }
    return $data;
  }

  /**
   * Returns the given data structure if iterable, or an array
   * containing the structure.
   *
   * @param  var $struct
   * @param  var
   */
  protected function iterableOf($struct) {
    if (is_array($struct) || $struct instanceof \Traversable) {
      return $struct;
    }
    return [$struct];
  }

  /**
   * Returns the first element of a given traversable data structure
   * or the data structure itself
   *
   * @param  var $struct
   * @param  var
   */
  protected function valueOf($struct) {
    if (is_array($struct) || $struct instanceof \Traversable) {
      foreach ($struct as $element) {
        return $element;
      }
    }
    return $struct;
  }

  /**
   * Returns a parameter's type or NULL if the given parameter does not exist
   *
   * @param  lang.reflect.Parameter $param
   * @return lang.Type
   */
  private function paramType($param) {
    return $param ? ($param->getTypeRestriction() ?: $param->getType()) : null;
  }
  
  /**
   * Convert data based on type
   *
   * @param   lang.Type type
   * @param   [:var] data
   * @return  var
   */
  public function unmarshal($type, $data) {
    if (null === $type || $type->equals(Type::$VAR)) {  // No conversion
      return $data;
    } else if (null === $data) {                        // Valid for any type
      return null;
    } else if ($type instanceof Primitive) {
      return $type->cast($this->valueOf($data));
    } else if ($type->equals(XPClass::forName('util.Date'))) {
      return $type->newInstance($data);
    } else if ($type instanceof XPClass) {
      if ($type->isInstance($data)) {
        return $data;
      }

      foreach ($this->marshallers->keys() as $t) {
        if ($t->isAssignableFrom($type)) return $this->marshallers[$t]->unmarshal($type, $data, $this);
      }

      // Check if a public static valueOf() method exists
      if ($type->hasMethod('valueOf')) {
        $valueOf= $type->getMethod('valueOf');
        if (Modifiers::isStatic($valueOf->getModifiers()) && Modifiers::isPublic($valueOf->getModifiers())) {
          if (1 === $valueOf->numParameters()) {
            return $valueOf->invoke(null, [$this->unmarshal($this->paramType($valueOf->getParameter(0)), $data)]);
          } else {
            $param= 0;
            $args= [];
            foreach ($this->iterableOf($data) as $value) {
              $args[]= $this->unmarshal($this->paramType($valueOf->getParameter($param++)), $value);
            }
            return $valueOf->invoke(null, $args);
          }
        }
      }

      // Generic approach
      $return= $type->newInstance();
      foreach ($this->iterableOf($data) as $name => $value) {
        foreach ($this->variantsOf($name) as $variant) {
          if ($type->hasField($variant)) {
            $field= $type->getField($variant);
            $m= $field->getModifiers();
            if ($m & MODIFIER_STATIC) {
              continue;
            } else if ($m & MODIFIER_PUBLIC) {
              if (null !== ($fType= $field->getType())) {
                $field->set($return, $this->unmarshal($fType, $value));
              } else {
                $field->set($return, $value);
              }
              continue 2;
            }
          }
          if ($type->hasMethod('set'.$variant)) {
            $method= $type->getMethod('set'.$variant);
            if ($method->getModifiers() & MODIFIER_PUBLIC) {
              if (null !== ($param= $method->getParameter(0))) {
                $method->invoke($return, [$this->unmarshal($param->getType(), $value)]);
              } else {
                $method->invoke($return, [$value]);
              }
              continue 2;
            }
          }
        }
      }
      return $return;
    } else if ($type instanceof \lang\ArrayType) {
      $return= [];
      foreach ($this->iterableOf($data) as $element) {
        $return[]= $this->unmarshal($type->componentType(), $element);
      }
      return $return;
    } else if ($type instanceof \lang\MapType) {
      $return= [];
      foreach ($this->iterableOf($data) as $key => $element) {
        $return[$key]= $this->unmarshal($type->componentType(), $element);
      }
      return $return;
    } else if ($type->equals(Type::$ARRAY)) {
      $return= [];
      foreach ($this->iterableOf($data) as $key => $element) {
        $return[$key]= $element;
      }
      return $return;
    } else {
      throw new \lang\FormatException('Cannot convert to '.\xp::stringOf($type));
    }
  }
}
