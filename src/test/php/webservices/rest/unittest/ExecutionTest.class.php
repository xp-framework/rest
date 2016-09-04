<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\Endpoint;
use webservices\rest\RestRequest;
use webservices\rest\RestException;
use io\streams\MemoryInputStream;
use peer\http\HttpConstants;
use lang\Type;
use lang\ClassLoader;

class ExecutionTest extends TestCase {
  private $fixture;
  private static $conn;

  #[@beforeClass]
  public static function dummyConnectionClass() {
    self::$conn= ClassLoader::defineClass('EndpointExecutionTest_Connection', 'peer.http.HttpConnection', [], '{
      private $result, $exception;

      public function __construct($status, $body, $headers) {
        parent::__construct("http://test");
        if ($status instanceof \lang\Throwable) {
          $this->exception= $status;
        } else {
          $this->result= "HTTP/1.1 ".$status."\r\n";
          foreach ($headers as $name => $value) {
            $this->result.= $name.": ".$value."\r\n";
          }
          $this->result.= "\r\n".$body;
        }
      }
      
      public function send(\peer\http\HttpRequest $request) {
        if ($this->exception) {
          throw $this->exception;
        } else {
          return new \peer\http\HttpResponse(new \io\streams\MemoryInputStream($this->result));
        }
      }
    }');
  }
  
  /**
   * Creates a new fixture
   *
   * @param   var status either an int with a status code or an exception object
   * @param   string body default NULL
   * @param   [:string] headers default [:]
   * @return  webservices.rest.Endpoint
   */
  public function fixtureWith($status, $body= null, $headers= []) {
    return (new Endpoint('http://test'))->usingConnections(function($url) use($status, $body, $headers) {
      return self::$conn->newInstance($status, $body, $headers);
    });
  }

  #[@test]
  public function status() {
    $fixture= $this->fixtureWith(HttpConstants::STATUS_OK, '');
    $response= $fixture->execute(new RestRequest());
    $this->assertEquals(HttpConstants::STATUS_OK, $response->status());
  }

  #[@test]
  public function content() {
    $fixture= $this->fixtureWith(HttpConstants::STATUS_NOT_FOUND, 'Error');
    $response= $fixture->execute(new RestRequest());
    $this->assertEquals('Error', $response->content());
  }

  #[@test, @expect(RestException::class)]
  public function exception() {
    $fixture= $this->fixtureWith(new \peer\ConnectException('Cannot connect'));
    $fixture->execute(new RestRequest());
  }
  
  #[@test]
  public function jsonContent() {
    $fixture= $this->fixtureWith(HttpConstants::STATUS_OK, '{ "title" : "Found a bug" }', [
      'Content-Type' => 'application/json'
    ]);
    $response= $fixture->execute(new RestRequest());
    $this->assertEquals(['title' => 'Found a bug'], $response->data());
  }

  #[@test]
  public function xmlContent() {
    $fixture= $this->fixtureWith(HttpConstants::STATUS_OK, '<issue><title>Found a bug</title></issue>', [
      'Content-Type' => 'text/xml'
    ]);
    $response= $fixture->execute(new RestRequest());
    $this->assertEquals(['title' => 'Found a bug'], $response->data(Type::forName('[:var]')));
  }
}
