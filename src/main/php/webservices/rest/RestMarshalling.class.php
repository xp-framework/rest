<?php namespace webservices\rest;

use lang\Enum;
use lang\XPClass;
use lang\Type;
use lang\Primitive;
use lang\reflect\Modifiers;
use lang\ClassLoader;

/**
 * Marshalling takes care of converting the value to a simple output 
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

    // Deprecated!
    if (PHP_VERSION < 7 && ClassLoader::getDefault()->providesPackage('lang.types')) {
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

    $this->marshallers[XPClass::forName('lang.Enum')]= newinstance('webservices.rest.TypeMarshaller', [], [
      'marshal'   => function($t) { return $t->name(); },
      'unmarshal' => function(Type $target, $in) { return Enum::valueOf($target, (string)$in); }
    ]);
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
   * Convert value
   *
   * @param   var value
   * @return  var
   */
  public function marshal($value) {
    if ($value instanceof \util\Date) {
      return $value->toString('c');    // ISO 8601, e.g. "2004-02-12T15:19:21+00:00"
    } else if ($value instanceof \Traversable) {
      return new Iteration($value, [$this, 'marshal']);
    } else if (is_object($value)) {
      foreach ($this->marshallers->keys() as $t) {      // Specific class marshalling
        if ($t->isInstance($value)) return $this->marshallers[$t]->marshal($value, $this);
      }

      $class= typeof($value);
      if ($class->hasAnnotation('recursive')) {
        $classToSearchIn= $class;
        $fields= [];
        do {
          $fields= array_merge($fields, $classToSearchIn->getFields());
        } while (
          $classToSearchIn->hasAnnotation('recursive') &&
          ($classToSearchIn= $classToSearchIn->getParentclass()) !== null
        );
      } else {
        $fields= $class->getFields();
      }
      $r= [];
      foreach ($fields as $field) {
        $m= $field->getModifiers();
        if ($m & MODIFIER_STATIC) {
          continue;
        } else if ($field->getModifiers() & MODIFIER_PUBLIC) {
          $r[$field->getName()]= $this->marshal($field->get($value));
        } else {
          foreach ($this->variantsOf($field->getName()) as $name) {
            if ($class->hasMethod($m= 'get'.$name)) {
              $r[$field->getName()]= $this->marshal($class->getMethod($m)->invoke($value));
              continue 2;
            }
          }
        }
      }
      return $r;
    } else if (is_array($value)) {
      $r= [];
      foreach ($value as $key => $val) {
        $r[$key]= $this->marshal($val);
      }
      return $r;
    }
    return $value;
  }

  /**
   * Returns the given value structure if iterable, or an array
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
   * Returns the first element of a given traversable value structure
   * or the value structure itself
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
   * Convert value based on type
   *
   * @param   lang.Type type
   * @param   [:var] value
   * @return  var
   */
  public function unmarshal($type, $value) {
    if (null === $type || $type->equals(Type::$VAR)) {  // No conversion
      return $value;
    } else if (null === $value) {                        // Valid for any type
      return null;
    } else if ($type instanceof Primitive) {
      return $type->cast($this->valueOf($value));
    } else if ($type->equals(XPClass::forName('util.Date'))) {
      return $type->newInstance($value);
    } else if ($type instanceof XPClass) {
      if ($type->isInstance($value)) {
        return $value;
      }

      foreach ($this->marshallers->keys() as $t) {
        if ($t->isAssignableFrom($type)) return $this->marshallers[$t]->unmarshal($type, $value, $this);
      }

      // Check if a public static valueOf() method exists
      if ($type->hasMethod('valueOf')) {
        $valueOf= $type->getMethod('valueOf');
        if (Modifiers::isStatic($valueOf->getModifiers()) && Modifiers::isPublic($valueOf->getModifiers())) {
          if (1 === $valueOf->numParameters()) {
            return $valueOf->invoke(null, [$this->unmarshal($this->paramType($valueOf->getParameter(0)), $value)]);
          } else {
            $param= 0;
            $args= [];
            foreach ($this->iterableOf($value) as $value) {
              $args[]= $this->unmarshal($this->paramType($valueOf->getParameter($param++)), $value);
            }
            return $valueOf->invoke(null, $args);
          }
        }
      }

      // Generic approach
      $return= $type->newInstance();
      foreach ($this->iterableOf($value) as $name => $value) {
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
      foreach ($this->iterableOf($value) as $element) {
        $return[]= $this->unmarshal($type->componentType(), $element);
      }
      return $return;
    } else if ($type instanceof \lang\MapType) {
      $return= [];
      foreach ($this->iterableOf($value) as $key => $element) {
        $return[$key]= $this->unmarshal($type->componentType(), $element);
      }
      return $return;
    } else if ($type->equals(Type::$ARRAY)) {
      $return= [];
      foreach ($this->iterableOf($value) as $key => $element) {
        $return[$key]= $element;
      }
      return $return;
    } else {
      throw new \lang\FormatException('Cannot convert to '.\xp::stringOf($type));
    }
  }
}
