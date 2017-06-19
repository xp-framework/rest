<?php namespace webservices\rest\unittest\srv;

use webservices\rest\TypeMarshaller;
use webservices\rest\srv\ExceptionMapper;
use unittest\TestCase;
use scriptlet\HttpScriptletRequest;
use scriptlet\HttpScriptletResponse;
use scriptlet\Cookie;
use webservices\rest\srv\RestContext;
use util\log\Logger;
use util\log\LogCategory;
use lang\reflect\Package;
use webservices\rest\unittest\srv\fixture\Greeting;

/**
 * Test default router
 *
 * @see  xp://webservices.rest.srv.RestDefaultRouter
 */
class RestContextTest extends TestCase {
  protected $fixture= null;
  protected static $package= null;

  /**
   * Sets up fixture package
   */
  #[@beforeClass]
  public static function fixturePackage() {
    self::$package= Package::forName('webservices.rest.unittest.srv.fixture');
  }

  /**
   * Setup
   */
  public function setUp() {
    $this->fixture= new RestContext();
  }

  /**
   * Returns a class object for a given fixture class
   *
   * @param  string $class
   * @return lang.XPClass
   */
  protected function fixtureClass($class) {
    return self::$package->loadClass($class);
  }

  /**
   * Returns a method from given fixture class
   *
   * @param  string $class
   * @param  string $method
   * @return lang.reflect.Method
   */
  protected function fixtureMethod($class, $method) {
    return self::$package->loadClass($class)->getMethod($method);
  }

  /**
   * Creates a new request with a given parameter map
   *
   * @param  [:string] params
   * @return scriptlet.Request
   */
  protected function newRequest($params= [], $payload= null, $headers= []) {
    $r= newinstance(HttpScriptletRequest::class, [$payload], '{
      public function __construct($payload) {
        if (null !== $payload) {
          $this->inputStream= new \io\streams\MemoryInputStream($payload);
        }
      }
    }');
    foreach ($params as $name => $value) {
      $r->setParam($name, $value);
    }
    if (isset($headers['Cookie'])) {
      foreach (explode(';', $headers['Cookie']) as $cookie) {
        sscanf(trim($cookie), '%[^=]=%s', $name, $value);
        $r->addCookie(new Cookie($name, $value));
      }
      unset($headers['Cookie']);
    }
    $r->setHeaders($headers);
    return $r;
  }

  /**
   * Assertion helper
   *
   * @param  int $status Expected status
   * @param  string[] $headers Expected headers
   * @param  string $content Expected content
   * @param  [:var] $route Route
   * @param  scriptlet.Request $request HTTP request
   * @throws unittest.AssertionFailedError
   */
  protected function assertProcess($status, $headers, $content, $route, $request) {
    $response= new HttpScriptletResponse();
    ob_start();

    $this->fixture->process($route, $request, $response);
    $this->assertEquals($status, $response->statusCode, 'Status code');
    $this->assertEquals($headers, $response->headers, 'Headers');

    $response->sendContent();
    $sent= ob_get_contents();
    ob_end_clean();
    $this->assertEquals($content, $sent, 'Content');
  }

  #[@test]
  public function marshal_this_generically() {
    $this->assertEquals(
      new \webservices\rest\Payload(['name' => $this->name]),
      $this->fixture->marshal(new \webservices\rest\Payload($this))
    );
  }

  #[@test]
  public function marshal_greeting_with_typemarshaller() {
    $this->fixture->addMarshaller(Greeting::class, newinstance(TypeMarshaller::class, [], '{
      public function marshal($t) {
        return $t->name;
      }
      public function unmarshal(\lang\Type $target, $name) {
        // Not needed
      }
    }'));
    $this->assertEquals(
      new \webservices\rest\Payload('World'),
      $this->fixture->marshal(new \webservices\rest\Payload(new Greeting('Hello', 'World')))
    );
  }

  #[@test]
  public function unmarshal_greeting_with_typemarshaller() {
    $this->fixture->addMarshaller(Greeting::class, newinstance(TypeMarshaller::class, [], '{
      public function marshal($t) {
        // Not needed
      }
      public function unmarshal(\lang\Type $target, $value) {
        return $target->newInstance("Hello", $value);
      }
    }'));
    $this->assertEquals(
      new Greeting('Hello', 'World'),
      $this->fixture->unmarshal(\lang\Type::forName(Greeting::class), 'World')
    );
  }

  #[@test]
  public function handle_xmlfactory_annotated_method() {
    $handler= newinstance('#[@webservice, @xmlfactory(element= "greeting")] webservices.rest.unittest.srv.Handler', [], '{
      #[@webmethod, @xmlfactory(element = "book")]
      public function getBook() {
        return array("isbn" => "978-3-16-148410-0", "author" => "Test");
      }
    }');
    $this->assertEquals(
      \webservices\rest\srv\Response::error(200)->withPayload(new \webservices\rest\Payload(['isbn' => '978-3-16-148410-0', 'author' => 'Test'], ['name' => 'book'])),
      $this->fixture->handle($handler, typeof($handler)->getMethod('getBook'), [])
    );
  }

  #[@test]
  public function handle_xmlfactory_annotated_class() {
    $handler= newinstance('#[@webservice, @xmlfactory(element= "greeting")] webservices.rest.unittest.srv.Handler', [], '{
      #[@webmethod]
      public function greet() { return "Test"; }
    }');
    $this->assertEquals(
      \webservices\rest\srv\Response::error(200)->withPayload(new \webservices\rest\Payload('Test', ['name' => 'greeting'])),
      $this->fixture->handle($handler, typeof($handler)->getMethod('greet'), [])
    );
  }

  #[@webmethod]
  public function raiseAnError($t) {
    throw $t;
  }

  #[@test]
  public function handle_exception_with_mapper() {
    $t= new \lang\Throwable('Test');
    $this->fixture->addExceptionMapping('lang.Throwable', newinstance(ExceptionMapper::class, [], '{
      public function asResponse($t, RestContext $ctx) {
        return Response::error(500)->withPayload(array("message" => $t->getMessage()));
      }
    }'));
    $this->assertEquals(
      \webservices\rest\srv\Response::status(500)->withPayload(new \webservices\rest\Payload(['message' => 'Test'], ['name' => 'exception'])),
      $this->fixture->handle($this, typeof($this)->getMethod('raiseAnError'), [$t])
    );
  }

  #[@test]
  public function constructor_injection() {
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_ConstructorInjection', 'webservices.rest.unittest.srv.Handler', [], '{
      protected $context;
      #[@inject(type = "webservices.rest.srv.RestContext")]
      public function __construct($context) { $this->context= $context; }
      public function equals($cmp) { return $cmp instanceof self && $this->context->equals($cmp->context); }
    }');
    $this->assertEquals(
      $class->newInstance($this->fixture),
      $this->fixture->handlerInstanceFor($class)
    );
  }

  #[@test]
  public function typename_injection() {
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_TypeNameInjection', 'webservices.rest.unittest.srv.Handler', [], '{
      protected $context;

      /** @param webservices.rest.srv.RestContext context */
      #[@inject]
      public function __construct($context) { $this->context= $context; }
      public function equals($cmp) { return $cmp instanceof self && $this->context->equals($cmp->context); }
    }');
    $this->assertEquals(
      $class->newInstance($this->fixture),
      $this->fixture->handlerInstanceFor($class)
    );
  }

  #[@test]
  public function typerestriction_injection() {
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_TypeRestrictionInjection', 'webservices.rest.unittest.srv.Handler', [], '{
      protected $context;

      #[@inject]
      public function __construct(\webservices\rest\srv\RestContext $context) { $this->context= $context; }
      public function equals($cmp) { return $cmp instanceof self && $this->context->equals($cmp->context); }
    }');
    $this->assertEquals(
      $class->newInstance($this->fixture),
      $this->fixture->handlerInstanceFor($class)
    );
  }

  #[@test]
  public function setter_injection() {
    $prop= new \util\Properties('service.ini');
    \util\PropertyManager::getInstance()->register('service', $prop);
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_SetterInjection', 'webservices.rest.unittest.srv.Handler', [], '{
      public $prop;
      #[@inject(type = "util.Properties", name = "service")]
      public function setServiceConfig($prop) { $this->prop= $prop; }
    }');
    $this->assertEquals(
      $prop,
      $this->fixture->handlerInstanceFor($class)->prop
    );
  }

  #[@test]
  public function unnamed_logcategory_injection() {
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_UnnamedLogcategoryInjection', 'webservices.rest.unittest.srv.Handler', [], '{
      public $cat;
      #[@inject(type = "util.log.LogCategory")]
      public function setTrace($cat) { $this->cat= $cat; }
    }');
    $cat= new LogCategory('test');
    $this->fixture->setTrace($cat);
    $this->assertEquals(
      $cat,
      $this->fixture->handlerInstanceFor($class)->cat
    );
  }

  #[@test]
  public function named_logcategory_injection() {
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_NamedLogcategoryInjection', 'webservices.rest.unittest.srv.Handler', [], '{
      public $cat;
      #[@inject(type = "util.log.LogCategory", name = "test")]
      public function setTrace($cat) { $this->cat= $cat; }
    }');
    $cat= Logger::getInstance()->getCategory('test');
    $this->assertEquals(
      $cat,
      $this->fixture->handlerInstanceFor($class)->cat
    );
  }

  #[@test, @expect(class = 'lang.reflect.TargetInvocationException', withMessage= '/InjectionError::setTrace/')]
  public function injection_error() {
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_InjectionError', 'webservices.rest.unittest.srv.Handler', [], '{
      #[@inject(type = "util.log.LogCategory")]
      public function setTrace($cat) { throw new \lang\IllegalStateException("Test"); }
    }');
    $this->fixture->handlerInstanceFor($class);
  }

  #[@test, @expect(class = 'lang.reflect.TargetInvocationException', withMessage= '/InstantiationError::<init>/')]
  public function instantiation_error() {
    $class= \lang\ClassLoader::defineClass('AbstractRestRouterTest_InstantiationError', 'webservices.rest.unittest.srv.Handler', [], '{
      public function __construct() { throw new \lang\IllegalStateException("Test"); }
    }');
    $this->fixture->handlerInstanceFor($class);
  }


  #[@test]
  public function greet_implicit_segment_and_param() {
    $route= [
      'handler'  => $this->fixtureClass('ImplicitGreetingHandler'),
      'target'   => $this->fixtureMethod('ImplicitGreetingHandler', 'greet'),
      'params'   => [],
      'segments' => [0 => '/implicit/greet/test', 'name' => 'test', 1 => 'test'],
      'input'    => null,
      'output'   => 'text/json'
    ];
    $this->assertEquals(
      ['test', 'Servus'],
      $this->fixture->argumentsFor($route, $this->newRequest(['greeting' => 'Servus']), \webservices\rest\RestFormat::$FORM)
    );
  }

  #[@test]
  public function greet_implicit_segment_and_missing_param() {
    $route= [
      'handler'  => $this->fixtureClass('ImplicitGreetingHandler'),
      'target'   => $this->fixtureMethod('ImplicitGreetingHandler', 'greet'),
      'params'   => [],
      'segments' => [0 => '/implicit/greet/test', 'name' => 'test', 1 => 'test'],
      'input'    => null,
      'output'   => 'text/json'
    ];
    $this->assertEquals(
      ['test', 'Hello'],
      $this->fixture->argumentsFor($route, $this->newRequest(), \webservices\rest\RestFormat::$FORM)
    );
  }

  #[@test]
  public function greet_implicit_payload() {
    $route= [
      'handler'  => $this->fixtureClass('ImplicitGreetingHandler'),
      'target'   => $this->fixtureMethod('ImplicitGreetingHandler', 'greet_posted'),
      'params'   => [],
      'segments' => [0 => '/greet'],
      'input'    => 'application/json',
      'output'   => 'text/json'
    ];
    $this->assertEquals(
      ['Hello World'],
      $this->fixture->argumentsFor($route, $this->newRequest([], '"Hello World"'), \webservices\rest\RestFormat::$JSON)
    );
  }

  #[@test]
  public function greet_intl() {
    $route= [
      'handler'  => $this->fixtureClass('GreetingHandler'),
      'target'   => $this->fixtureMethod('GreetingHandler', 'greet_intl'),
      'params'   => ['language' => new \webservices\rest\srv\RestParamSource('Accept-Language', \webservices\rest\srv\ParamReader::$HEADER)],
      'segments' => [0 => '/intl/greet/test', 'name' => 'test', 1 => 'test'],
      'input'    => null,
      'output'   => 'text/json'
    ];
    $this->assertEquals(
      ['test', new \scriptlet\Preference('de')],
      $this->fixture->argumentsFor($route, $this->newRequest([], null, ['Accept-Language' => 'de']), \webservices\rest\RestFormat::$FORM)
    );
  }

  #[@test]
  public function greet_user() {
    $route= [
      'handler'  => $this->fixtureClass('GreetingHandler'),
      'target'   => $this->fixtureMethod('GreetingHandler', 'greet_user'),
      'params'   => ['name' => new \webservices\rest\srv\RestParamSource('user', \webservices\rest\srv\ParamReader::$COOKIE)],
      'segments' => [0 => '/user/greet'],
      'input'    => null,
      'output'   => 'text/json'
    ];
    $this->assertEquals(
      ['Test'],
      $this->fixture->argumentsFor($route, $this->newRequest([], null, ['Cookie' => 'user=Test']), \webservices\rest\RestFormat::$FORM)
    );
  }


  #[@test]
  public function process_greet_successfully() {
    $route= [
      'handler'  => $this->fixtureClass('GreetingHandler'),
      'target'   => $this->fixtureMethod('GreetingHandler', 'greet'),
      'params'   => ['name' => new \webservices\rest\srv\RestParamSource('name', \webservices\rest\srv\ParamReader::$PATH)],
      'segments' => [0 => '/greet/Test', 'name' => 'Test', 1 => 'Test'],
      'input'    => null,
      'output'   => 'text/json'
    ];
    $this->assertProcess(
      200, ['Content-Type: text/json'], '"Hello Test"',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function process_greet_with_missing_parameter() {
    $route= [
      'handler'  => $this->fixtureClass('GreetingHandler'),
      'target'   => $this->fixtureMethod('GreetingHandler', 'greet'),
      'params'   => ['name' => new \webservices\rest\srv\RestParamSource('name', \webservices\rest\srv\ParamReader::$PATH)],
      'segments' => [0 => '/greet/'],
      'input'    => null,
      'output'   => 'text/json'
    ];
    $this->assertProcess(
      400, ['Content-Type: text/json'], '{"message":"Parameter \"name\" required, but not found in path(\'name\')"}',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function process_greet_and_go() {
    $route= [
      'handler'  => $this->fixtureClass('GreetingHandler'),
      'target'   => $this->fixtureMethod('GreetingHandler', 'greet_and_go'),
      'params'   => ['name' => new \webservices\rest\srv\RestParamSource('name', \webservices\rest\srv\ParamReader::$PATH)], 
      'segments' => [0 => '/greet/and/go/test', 'name' => 'test', 1 => 'test'],
      'input'    => null,
      'output'   => 'text/json'
    ];
    $this->assertProcess(
      204, [], '',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function marshal_exceptions() {
    $this->fixture->addMarshaller('unittest.AssertionFailedError', newinstance(TypeMarshaller::class, [], '{
      public function marshal($t) {
        return "assert:".$t->message;
      }
      public function unmarshal(\lang\Type $target, $name) {
        // Not needed
      }
    }'));
    $this->assertEquals(
      \webservices\rest\srv\Response::error(500)->withPayload(new \webservices\rest\Payload('assert:expected 1 but was 2', ['name' => 'exception'])),
      $this->fixture->asResponse(new \unittest\AssertionFailedError('expected 1 but was 2'))
    );
  }

  #[@test]
  public function process_streaming_output() {
    $route= [
      'handler'  => $this->fixtureClass('GreetingHandler'),
      'target'   => $this->fixtureMethod('GreetingHandler', 'download_greeting'),
      'params'   => [],
      'segments' => [0 => '/download'],
      'input'    => null,
      'output'   => null
    ];

    $this->assertProcess(
      200, ['Content-Type: text/plain; charset=utf-8', 'Content-Length: 11'], 'Hello World',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function process_extended() {
    $extended= \lang\ClassLoader::defineClass(
      'webservices.rest.unittest.srv.fixture.GreetingHandlerExtended',
      $this->fixtureClass('GreetingHandler')->getName(),
      [],
      '{}'
    );

    $route= [
      'handler'  => $extended,
      'target'   => $extended->getMethod('greet_class'),
      'params'   => [],
      'segments' => [0 => '/greet/class'],
      'input'    => null,
      'output'   => 'text/json'
    ];

    $this->assertProcess(
      200, ['Content-Type: text/json'], '"Hello '.$extended->getName().'"',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function add_exception_mapping_returns_added_mapping() {
    $mapping= newinstance(ExceptionMapper::class, [], '{
      public function asResponse($t, RestContext $ctx) {
        return Response::error(500)->withPayload(array("message" => $t->getMessage()));
      }
    }');
    $this->assertEquals($mapping, $this->fixture->addExceptionMapping('lang.Throwable', $mapping));
  }

  #[@test]
  public function get_exception_mapping() {
    $mapping= newinstance(ExceptionMapper::class, [], '{
      public function asResponse($t, RestContext $ctx) {
        return Response::error(500)->withPayload(array("message" => $t->getMessage()));
      }
    }');
    $this->fixture->addExceptionMapping('lang.Throwable', $mapping);
    $this->assertEquals($mapping, $this->fixture->getExceptionMapping('lang.Throwable'));
  }

  #[@test]
  public function get_non_existant_exception_mapping() {
    $this->assertNull($this->fixture->getExceptionMapping('unittest.AssertionFailedError'));
  }

  #[@test]
  public function add_marshaller_returns_added_marshaller() {
    $marshaller= newinstance(TypeMarshaller::class, [], '{
      public function marshal($t) {
        return $t->getName();
      }
      public function unmarshal(\lang\Type $target, $name) {
        // Not needed
      }
    }');
    $this->assertEquals($marshaller, $this->fixture->addMarshaller('unittest.TestCase', $marshaller));
  }

  #[@test]
  public function get_marshaller() {
    $marshaller= newinstance(TypeMarshaller::class, [], '{
      public function marshal($t) {
        return $t->getName();
      }
      public function unmarshal(\lang\Type $target, $name) {
        // Not needed
      }
    }');
    $this->fixture->addMarshaller('unittest.TestCase', $marshaller);
    $this->assertEquals($marshaller, $this->fixture->getMarshaller('unittest.TestCase'));
  }

  #[@test]
  public function get_non_existant_marshaller() {
    $this->assertNull($this->fixture->getMarshaller('unittest.TestCase'));
  }

  #[@test]
  public function process_exceptions_from_handler_constructor() {
    $route= [
      'handler'  => $this->fixtureClass('RaisesErrorFromConstructor'),
      'target'   => null,
      'params'   => [],
      'segments' => [],
      'input'    => null,
      'output'   => 'text/json'
    ];

    $this->assertProcess(
      500, ['Content-Type: text/json'], '{"message":"Cannot instantiate"}',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function process_exceptions_from_handler_constructor_are_not_mapped() {
    $route= [
      'handler'  => $this->fixtureClass('RaisesExceptionFromConstructor'),
      'target'   => null,
      'params'   => [],
      'segments' => [],
      'input'    => null,
      'output'   => 'text/json'
    ];

    $this->assertProcess(
      500, ['Content-Type: text/json'], '{"message":"Cannot instantiate"}',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function process_errors_from_handler_method() {
    $route= [
      'handler'  => $this->fixtureClass('RaisesFromMethod'),
      'target'   => $this->fixtureMethod('RaisesFromMethod', 'error'),
      'params'   => [],
      'segments' => [],
      'input'    => null,
      'output'   => 'text/json'
    ];

    $this->assertProcess(
      500, ['Content-Type: text/json'], '{"message":"Invocation failed"}',
      $route, $this->newRequest()
    );
  }

  #[@test]
  public function process_exceptions_from_handler_method_are_mapped() {
    $route= [
      'handler'  => $this->fixtureClass('RaisesFromMethod'),
      'target'   => $this->fixtureMethod('RaisesFromMethod', 'exception'),
      'params'   => [],
      'segments' => [],
      'input'    => null,
      'output'   => 'text/json'
    ];

    $this->assertProcess(
      409, ['Content-Type: text/json'], '{"message":"Invocation failed"}',
      $route, $this->newRequest()
    );
  }
}
