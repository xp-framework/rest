<?php namespace webservices\rest\srv;

use peer\http\HttpConstants;
use scriptlet\Preference;
use webservices\rest\RestMarshalling;
use webservices\rest\RestFormat;
use webservices\rest\Payload;
use webservices\rest\TypeMarshaller;
use util\collections\HashTable;
use util\PropertyManager;
use util\log\Logger;
use lang\XPClass;
use lang\Type;
use lang\Throwable;
use lang\FormatException;
use lang\IllegalStateException;
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;

/**
 * The context of a rest call
 *
 * @test  xp://net.xp_framework.unittest.webservices.rest.srv.RestContextTest
 * @test  xp://net.xp_framework.unittest.webservices.rest.srv.RestContextHandleTest
 */
class RestContext implements \util\log\Traceable {
  protected $mappers;
  protected $marshalling;
  protected $cat= null;

  /**
   * Constructor
   */
  public function __construct() {
    $this->mappers= create('new util.collections.HashTable<lang.XPClass, webservices.rest.srv.ExceptionMapper>');
    $this->marshalling= new RestMarshalling();

    // Default exception mappings
    $this->addExceptionMapping('lang.IllegalAccessException', new DefaultExceptionMapper(403));
    $this->addExceptionMapping('lang.IllegalArgumentException', new DefaultExceptionMapper(400));
    $this->addExceptionMapping('lang.IllegalStateException', new DefaultExceptionMapper(409));
    $this->addExceptionMapping('lang.ElementNotFoundException', new DefaultExceptionMapper(404));
    $this->addExceptionMapping('lang.MethodNotImplementedException', new DefaultExceptionMapper(501));
    $this->addExceptionMapping('lang.FormatException', new DefaultExceptionMapper(422));

    $this->addMarshaller('lang.Throwable', new DefaultExceptionMarshaller());
  }

  /**
   * Adds an exception mapper
   *
   * @param  var type either a full qualified class name or an XPClass instance
   * @param  webservices.rest.srv.ExceptionMapper m
   * @return webservices.rest.srv.ExceptionMapper The added mapper
   */
  public function addExceptionMapping($type, ExceptionMapper $m) {
    $this->mappers[$type instanceof XPClass ? $type : XPClass::forName($type)]= $m;
    return $m;
  }

  /**
   * Gets an exception mapper
   *
   * @param  var type either a full qualified class name or an XPClass instance
   * @return webservices.rest.srv.ExceptionMapper or NULL if no mapper exists
   */
  public function getExceptionMapping($type) {
    return $this->mappers[$type instanceof XPClass ? $type : XPClass::forName($type)];
  }

  /**
   * Adds a type marshaller
   *
   * @param  var type either a full qualified type name or a type instance
   * @param  webservices.rest.TypeMarshaller m
   * @return webservices.rest.TypeMarshaller The added marshaller
   */
  public function addMarshaller($type, TypeMarshaller $m) {
    return $this->marshalling->addMarshaller($type, $m);
  }

  /**
   * Adds a type marshaller
   *
   * @param  var type either a full qualified type name or a type instance
   * @return webservices.rest.TypeMarshaller The added marshaller
   */
  public function getMarshaller($type) {
    return $this->marshalling->getMarshaller($type);
  }

  /**
   * Finds the exception mapper for the given throwable instance
   *
   * @param  lang.Throwable $throwale
   * @return webservices.rest.srv.ExceptionMapper or NULL if no mapper exists
   */
  public function findMapper($throwable) {
    foreach ($this->mappers->keys() as $type) {
      if ($type->isInstance($throwable)) return $this->mappers[$type];
    }
    return null;
  }

  /**
   * Maps an exception to a response, using the default exception mapper if 
   * no more specific mapper is given.
   *
   * @param  lang.Throwable $t
   * @param  webservices.rest.srv.ExceptionMapper $mapper
   * @return webservices.rest.srv.Response
   */
  public function asResponse($t, ExceptionMapper $mapper= null) {
    static $properties= ['name' => 'exception'];   // XML root node

    if (null === $mapper) {
      $mapper= new DefaultExceptionMapper(HttpConstants::STATUS_INTERNAL_SERVER_ERROR);
    }

    $response= $mapper->asResponse($t, $this);
    $response->payload->properties= $properties;
    return $response;
  }

  /**
   * Marshal a type
   *
   * @param  webservices.rest.Payload payload
   * @return webservices.rest.Payload
   */
  public function marshal(Payload $payload= null, $properties= []) {
    if (null === $payload) {
      return null;
    } else {
      $marshalled= $this->marshalling->marshal($payload->value);
      return null === $marshalled ? null : new Payload($marshalled, $properties);
    }
  }

  /**
   * Unmarshal a type to a given target
   *
   * @param  lang.Type target
   * @param  var in
   * @return webservices.rest.srv.Response
   */
  public function unmarshal(Type $target, $in) {
    return $this->marshalling->unmarshal($target, $in);
  }

  /**
   * Returns arguments used for injection 
   *
   * @param  lang.reflect.Routine routine
   * @return var[] args
   */
  protected function injectionArgs($routine) {
    if ($routine->numParameters() < 1) return [];

    $inject= $routine->getAnnotation('inject');
    $type= isset($inject['type']) ? $inject['type'] : $routine->getParameter(0)->getType()->getName();
    switch ($type) {
      case 'util.log.LogCategory': 
        $args= [isset($inject['name']) ? Logger::getInstance()->getCategory($inject['name']) : $this->cat];
        break;

      case 'util.Properties': 
        $args= [PropertyManager::getInstance()->getProperties($inject['name'])];
        break;

      case 'webservices.rest.srv.RestContext':
        $args= [$this];
        break;

      default:
        throw new IllegalStateException('Unkown injection type '.$type);
    }

    return $args;
  }

  /**
   * Creates a handler instance
   *
   * @param  lang.XPClass class
   * @return lang.Generic instance
   * @throws lang.reflect.TargetInvocationException If the constructor or routines used for injection raise an exception
   */
  public function handlerInstanceFor($class) {

    // Constructor injection
    if ($class->hasConstructor()) {
      $c= $class->getConstructor();
      $instance= $c->newInstance($c->hasAnnotation('inject') ? $this->injectionArgs($c) : []);
    } else {
      $instance= $class->newInstance();
    }

    // Method injection
    foreach ($class->getMethods() as $m) {
      if ($m->hasAnnotation('inject')) $m->invoke($instance, $this->injectionArgs($m));
    }
    return $instance;
  }

  /**
   * Handle routing item
   *
   * @param  lang.Oject instance
   * @param  lang.reflect.Method method
   * @param  var[] args
   * @param  webservices.rest.srv.RestContext context
   * @return webservices.rest.srv.Response
   */
  public function handle($instance, $method, $args) {

    // HACK: Ungeneric XML-related
    $properties= [];
    if ($method->hasAnnotation('xmlfactory', 'element')) {
      $properties['name']= $method->getAnnotation('xmlfactory', 'element');
    } else if (($class= $method->getDeclaringClass()) && $class->hasAnnotation('xmlfactory', 'element')) {
      $properties['name']= $class->getAnnotation('xmlfactory', 'element');
    }

    // Invoke the method
    try {
      $result= $method->invoke($instance, $args);
      $this->cat && $this->cat->debug('<-', $result);
    } catch (TargetInvocationException $e) {
      $this->cat && $this->cat->warn('<-', $e);
      $cause= $e->getCause();
      return $this->asResponse($cause, $this->findMapper($cause));
    }

    // For "VOID" methods, set status to "no content". If a response is returned, 
    // use its status, headers and payload. For any other methods, set status to "OK".
    if (Type::$VOID->equals($method->getReturnType())) {
      return Response::status(HttpConstants::STATUS_NO_CONTENT);
    } else if ($result instanceof Output) {
      $result->payload= $this->marshal($result->payload, $properties);
      return $result;
    } else {
      $payload= $this->marshal(new Payload($result), $properties);
      return Response::status(HttpConstants::STATUS_OK)->withPayload($payload);
    }
  }

  /**
   * Read arguments from request
   *
   * @param  [:var] target
   * @param  scriptlet.Request request
   * @return var[] args
   */
  public function argumentsFor($target, $request) {
    $args= [];
    foreach ($target['target']->getParameters() as $parameter) {
      $param= $parameter->getName();

      // Extract arguments according to definition. In case we don't have an explicit
      // source for an argument, look up according to the following rules:
      //
      // * If we have a segment named exactly like the parameter, use it
      // * If there is no incoming payload, check the parameters
      // * If there is an incoming payload, use that.
      //
      // Handle explicitely configured sources first.
      if (isset($target['params'][$param])) {
        $src= $target['params'][$param];
      } else if (isset($target['segments'][$param])) {
        $src= new RestParamSource($param, ParamReader::$PATH);
      } else if (null === $target['input']) {
        $src= new RestParamSource($param, ParamReader::$PARAM);
      } else {
        $src= new RestParamSource(null, ParamReader::$BODY);
      }

      if (null === ($arg= $src->reader->read($src->name, $target, $request))) {
        if ($parameter->isOptional()) {
          $arg= $parameter->getDefaultValue();
        } else {
          throw new IllegalArgumentException('Parameter "'.$param.'" required, but not found in '.$src->toString());
        }
      }
      $args[]= $this->unmarshal($parameter->getType(), $arg);
    }
    return $args;
  }

  /**
   * Process a request
   *
   * @param   [:var] target
   * @param   scriptlet.Request request
   * @param   scriptlet.Response response
   * @return  bool
   */
  public function process($target, $request, $response) {
    $this->cat && $this->cat->debug('->', $target);

    try {
      $result= $this->handle(
        $this->handlerInstanceFor($target['handler']),
        $target['target'],
        $this->argumentsFor($target, $request)
      );
    } catch (TargetInvocationException $e) {
      $this->cat && $this->cat->error('<-', $e);
      $result= $this->asResponse($e->getCause(), null);
    } catch (FormatException $e) {
      $this->cat && $this->cat->error('<-', $e);
      $result= $this->asResponse($e, new DefaultExceptionMapper(HttpConstants::STATUS_UNSUPPORTED_MEDIA_TYPE));
    } catch (IllegalArgumentException $e) {
      $this->cat && $this->cat->error('<-', $e);
      $result= $this->asResponse($e, new DefaultExceptionMapper(HttpConstants::STATUS_BAD_REQUEST));
    } catch (Throwable $t) {
      $this->cat && $this->cat->error('<-', $t);
      $result= $this->asResponse($t, null);
    }

    // Have a result
    return $result->writeTo($response, $request->getURL(), $target['output']);
  }

  /**
   * Set a log category for tracing
   *
   * @param  util.log.LogCategory cat
   */
  public function setTrace($cat) {
    $this->cat= $cat;
  }

  /**
   * Returns whether a given value is equal to this context instance
   *
   * @param  var cmp
   * @return bool
   */
  public function equals($cmp) {
    return (
      $cmp instanceof self && 
      $this->mappers->equals($cmp->mappers)
    );
  }
}
