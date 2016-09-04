<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestClient;
use webservices\rest\RestRequest;
use webservices\rest\RestFormat;
use io\streams\MemoryInputStream;
use peer\http\HttpConnection;
use peer\http\HttpConstants;
use peer\http\RequestData;
use lang\ClassLoader;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestClient
 */
class RestClientSendTest extends TestCase {
  private static $conn;
  private $fixture;

  /** @return void */
  public function setUp() {
    $this->fixture= new RestClient('http://test');
    $this->fixture->setConnection(self::$conn->newInstance('http://test'));
  }

  #[@beforeClass]
  public static function requestEchoingConnectionClass() {
    self::$conn= ClassLoader::defineClass('RestClientSendTest_Connection', HttpConnection::class, [], '{
      public function send(\peer\http\HttpRequest $request) {
        $str= $request->getRequestString();
        return new \peer\http\HttpResponse(new \io\streams\MemoryInputStream(sprintf(
          "HTTP/1.0 200 OK\r\nContent-Type: text/plain\r\nContent-Length: %d\r\n\r\n%s",
          strlen($str),
          $str
        )));
      }
    }');
  }

  #[@test]
  public function with_body() {
    $this->assertEquals(
      "POST / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Length: 5\r\n".
      "Content-Type: application/x-www-form-urlencoded\r\n".
      "\r\n".
      "Hello",
      $this->fixture->execute((new RestRequest('/', HttpConstants::POST))->withBody(new RequestData('Hello')))->content()
    );
  }

  #[@test]
  public function with_json_payload() {
    $this->assertEquals(
      "POST / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json; charset=utf-8\r\n".
      "Content-Length: 6\r\n".
      "\r\n".
      "\"Test\"",
      $this->fixture->execute((new RestRequest('/', HttpConstants::POST))->withPayload('Test', RestFormat::$JSON))->content()
    );
  }

  #[@test]
  public function with_xml_payload() {
    $this->assertEquals(
      "POST / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: text/xml; charset=utf-8\r\n".
      "Content-Length: 56\r\n".
      "\r\n".
      "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
      "<root>Test</root>",
      $this->fixture->execute((new RestRequest('/', HttpConstants::POST))->withPayload('Test', RestFormat::$XML))->content()
    );
  }

  #[@test]
  public function with_form_data_payload() {
    $this->assertEquals(
      "POST / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n".
      "Content-Length: 9\r\n".
      "\r\n".
      "key=value",
      $this->fixture->execute((new RestRequest('/', HttpConstants::POST))->withPayload(['key' => 'value'], RestFormat::$FORM))->content()
    );
  }
}
