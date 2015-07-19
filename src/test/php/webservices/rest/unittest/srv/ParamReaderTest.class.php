<?php namespace webservices\rest\unittest\srv;

use webservices\rest\srv\ParamReader;
use scriptlet\Cookie;

class ParamReaderTest extends \unittest\TestCase {

  /**
   * Creates a new request with a given parameter map
   *
   * @param  [:string] params
   * @param  string $payload
   * @param  [:string] $headers
   * @return scriptlet.Request
   */
  private function newRequest($params= [], $payload= null, $headers= []) {
    $r= newinstance('scriptlet.HttpScriptletRequest', [$payload], '{
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

  #[@test, @values(['cookie', 'header', 'param', 'path', 'body'])]
  public function can_create($name) {
    ParamReader::forName($name);
  }

  #[@test]
  public function cookie() {
    $this->assertEquals('test', ParamReader::$COOKIE->read('name', [], $this->newRequest([], '', ['Cookie' => 'name=test'])));
  }

  #[@test]
  public function header() {
    $this->assertEquals('test', ParamReader::$HEADER->read('X-Name', [], $this->newRequest([], '', ['X-Name' => 'test'])));
  }

  #[@test]
  public function param() {
    $this->assertEquals('test', ParamReader::$PARAM->read('name', [], $this->newRequest(['name' => 'test'], '', [])));
  }

  #[@test]
  public function param_via_name() {
    $this->assertEquals('test', ParamReader::$PARAM->read(['name' => 't'], [], $this->newRequest(['t' => 'test'], '', [])));
  }

  #[@test, @values([
  #  [['color' => 'green', 'price' => '$12.99']],
  #  [['price' => '$12.99', 'color' => 'green']]
  #])]
  public function params($input) {
    $this->assertEquals(
      ['color' => 'green', 'price' => '$12.99'],
      ParamReader::$PARAM->read(['color', 'price'], [], $this->newRequest($input, '', []))
    );
  }

  #[@test, @values([
  #  [['color' => 'green', 'price' => '$12.99']],
  #  [['price' => '$12.99', 'color' => 'green']]
  #])]
  public function params_via_names($input) {
    $this->assertEquals(
      ['color' => 'green', 'price' => '$12.99'],
      ParamReader::$PARAM->read(['names' => ['color', 'price']], [], $this->newRequest($input, '', []))
    );
  }

  #[@test]
  public function path() {
    $this->assertEquals('test', ParamReader::$PATH->read('name', ['segments' => ['name' => 'test']], $this->newRequest([], '', [])));
  }

  #[@test]
  public function body() {
    $this->assertEquals('test', ParamReader::$BODY->read('name', ['input' => 'application/json'], $this->newRequest([], '"test"', [])));
  }

  #[@test]
  public function use_with_empty() {
    $this->assertEquals(
      [null, 'Test'],
      ParamReader::$PARAM->read(['use' => ['Test']], [], $this->newRequest([], '', []))
    );
  }

  #[@test]
  public function use_with_name() {
    $this->assertEquals(
      ['test', 'Test'],
      ParamReader::$PARAM->read(['name' => 't', 'use' => ['Test']], [], $this->newRequest(['t' => 'test'], '', []))
    );
  }

  #[@test]
  public function use_with_names() {
    $this->assertEquals(
      ['t1' => 'a', 't2' => 'b', 'c'],
      ParamReader::$PARAM->read(['names' => ['t1', 't2'], 'use' => ['c']], [], $this->newRequest(['t1' => 'a', 't2' => 'b'], '', []))
    );
  }
}