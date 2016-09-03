<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestClient;
use webservices\rest\RestRequest;
use webservices\rest\RestException;
use io\streams\MemoryInputStream;
use peer\http\HttpConstants;
use lang\Type;
use lang\ClassLoader;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestClient
 */
class RestClientExecutionTest extends TestCase {
  protected $fixture= null;
  protected static $conn= null;   

  #[@beforeClass]
  public static function dummyConnectionClass() {
    self::$conn= ClassLoader::defineClass('RestClientExecutionTest_Connection', 'peer.http.HttpConnection', [], '{
      protected $result= NULL;
      protected $exception= NULL;

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
   * @return  webservices.rest.RestClient
   */
  public function fixtureWith($status, $body= null, $headers= []) {
    return (new RestClient('http://test'))->usingConnections(function($url) use($status, $body, $headers) {
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
