<?php namespace webservices\rest\unittest\srv;

use lang\Object;
use webservices\rest\srv\RestContext;
use webservices\rest\srv\Response;
use webservices\rest\Payload;
use lang\IllegalArgumentException;
use lang\IllegalAccessException;
use lang\ElementNotFoundException;
use lang\IllegalStateException;
use lang\FormatException;
use lang\XPException;
use lang\MethodNotImplementedException;
use unittest\actions\RuntimeVersion;

/**
 * Test RestContext::handle() 
 *
 * @see  xp://webservices.rest.srv.RestContext
 */
class RestContextHandleTest extends \unittest\TestCase {

  /**
   * Convenience wrapper around RestContext::handle()
   *
   * @param  lang.Generic $instance
   * @param  var[] $args
   * @return webservices.rest.srv.Response
   */
  protected function handle($instance, $args= []) {
    return (new RestContext())->handle($instance, $instance->getClass()->getMethod('fixture'), $args);
  }

  #[@test]
  public function primitive_return() {
    $handler= newinstance(Object::class, [], '{
      #[@webmethod(verb= "GET")]
      public function fixture() { return "Hello World"; }
    }');
    $this->assertEquals(
      Response::status(200)->withPayload(new Payload('Hello World')),
      $this->handle($handler)
    );
  }

  #[@test]
  public function response_instance_return() {
    $handler= newinstance(Object::class, [], '{
      #[@webmethod(verb= "GET")]
      public function fixture() { return \webservices\rest\srv\Response::created("/resource/4711"); }
    }');
    $this->assertEquals(
      Response::status(201)->withHeader('Location', '/resource/4711'),
      $this->handle($handler)
    );
  }

  #[@test]
  public function void_return() {
    $handler= newinstance(Object::class, [], '{
      /** @return void **/
      #[@webmethod(verb= "GET")]
      public function fixture() { /* Intentionally empty */ }
    }');
    $this->assertEquals(
      Response::status(204),
      $this->handle($handler)
    );
  }

  #[@test]
  public function void_return_ignores_return_value() {
    $handler= newinstance(Object::class, [], '{
      /** @return void **/
      #[@webmethod(verb= "GET")]
      public function fixture() { return "Something"; }
    }');
    $this->assertEquals(
      Response::status(204),
      $this->handle($handler)
    );
  }

  #[@test]
  public function null_return() {
    $handler= newinstance(Object::class, [], '{
      #[@webmethod(verb= "GET")]
      public function fixture() { return null; }
    }');
    $this->assertEquals(
      Response::status(200)->withPayload(null),
      $this->handle($handler)
    );
  }

  #[@test]
  public function no_return() {
    $handler= newinstance(Object::class, [], '{
      #[@webmethod(verb= "GET")]
      public function fixture() { return; }
    }');
    $this->assertEquals(
      Response::status(200)->withPayload(null),
      $this->handle($handler)
    );
  }

  #[@test, @action(new RuntimeVersion('<7.0.0-dev'))]
  public function handle_string_class_in_parameters_and_return() {
    $handler= newinstance(Object::class, [], '{
      #[@webmethod(verb= "GET")]
      public function fixture(\lang\types\String $name) {
        if ($name->startsWith("www.")) {
          return array("name" => $name->substring(4));
        } else {
          return array("name" => $name);
        }
      }
    }');
    $this->assertEquals(
      Response::status(200)->withPayload(new Payload(['name' => 'example.com'])),
      $this->handle($handler, [new \lang\types\String('example.com')])
    );
  }

  #[@test, @values([
  #  [400, IllegalArgumentException::class],
  #  [403, IllegalAccessException::class],
  #  [404, ElementNotFoundException::class],
  #  [409, IllegalStateException::class],
  #  [422, FormatException::class],
  #  [500, XPException::class],
  #  [501, MethodNotImplementedException::class]
  #])]
  public function raised_exception($status, $exception) {
    $handler= newinstance(Object::class, [$exception], [
      'exception' => null,
      '__construct' => function($exception) {
        $this->exception= $exception;
      },
      '#[@webmethod(verb= "GET")] fixture' => function() {
        throw new $this->exception('Test', null);
      }
    ]);
    $this->assertEquals(
      Response::error($status)->withPayload(new Payload(['message' => 'Test'], ['name' => 'exception'])),
      $this->handle($handler)
    );
  }
}
