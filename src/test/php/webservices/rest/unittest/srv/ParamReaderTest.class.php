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
  public function path() {
    $this->assertEquals('test', ParamReader::$PATH->read('name', ['segments' => ['name' => 'test']], $this->newRequest([], '', [])));
  }

  #[@test]
  public function body() {
    $this->assertEquals('test', ParamReader::$BODY->read('name', ['input' => 'application/json'], $this->newRequest([], '"test"', [])));
  }
}