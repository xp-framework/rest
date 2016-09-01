<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestClient;
use webservices\rest\RestRequest;
use webservices\rest\RestFormat;
use io\streams\MemoryInputStream;
use peer\http\HttpConstants;
use peer\http\RequestData;
use lang\ClassLoader;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestClient
 */
class RestClientSendTest extends TestCase {
  protected static $conn= null;   
  protected $fixture= null;

  /**
   * Creates connection class which echoes the request
   */
  #[@beforeClass]
  public static function requestEchoingConnectionClass() {
    self::$conn= ClassLoader::defineClass('RestClientSendTest_Connection', 'peer.http.HttpConnection', [], '{
      public function __construct() {
        parent::__construct("http://test");
      }
      
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

  /**
   * Creates fixture.
   */
  public function setUp() {
    $this->fixture= new RestClient();
    $this->fixture->setConnection(self::$conn->newInstance());
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

  #[@test]
  public function get() {
    $this->assertEquals(
      "GET / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->get('/')->content()
    );
  }

  #[@test]
  public function get_with_segment() {
    $this->assertEquals(
      "GET /user/6100 HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->get(['/user/{id}', 'id' => 6100])->content()
    );
  }

  #[@test]
  public function get_with_parameter() {
    $this->assertEquals(
      "GET /users?page=1 HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->get('/users', ['page' => 1])->content()
    );
  }

  #[@test]
  public function post() {
    $this->assertEquals(
      "POST /user HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 15\r\n".
      "\r\n".
      "{\"name\":\"Test\"}",
      $this->fixture->post('/user', ['name' => 'Test'], 'application/json')->content()
    );
  }

  #[@test]
  public function post_with_segment() {
    $this->assertEquals(
      "POST /user/6100/emails HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 30\r\n".
      "\r\n".
      "{\"address\":\"test@example.com\"}",
      $this->fixture->post(['/user/{id}/emails', 'id' => 6100], ['address' => 'test@example.com'], 'application/json')->content()
    );
  }

  #[@test]
  public function put() {
    $this->assertEquals(
      "PUT /user/self HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 15\r\n".
      "\r\n".
      "{\"name\":\"Test\"}",
      $this->fixture->put('/user/self', ['name' => 'Test'], 'application/json')->content()
    );
  }

  #[@test]
  public function put_with_segment() {
    $this->assertEquals(
      "PUT /user/0 HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 15\r\n".
      "\r\n".
      "{\"name\":\"Test\"}",
      $this->fixture->put(['/user/{id}', 'id' => 0], ['name' => 'Test'], 'application/json')->content()
    );
  }

  #[@test]
  public function delete() {
    $this->assertEquals(
      "DELETE /user/1 HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->delete('/user/1')->content()
    );
  }

  #[@test]
  public function delete_with_segment() {
    $this->assertEquals(
      "DELETE /user/1 HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->delete(['/user/{id}', 'id' => 1])->content()
    );
  }

  #[@test]
  public function delete_with_parameter() {
    $this->assertEquals(
      "DELETE /user?name=test HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->delete('/user', ['name' => 'test'])->content()
    );
  }
}