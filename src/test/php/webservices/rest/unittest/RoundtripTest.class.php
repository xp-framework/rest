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

class RoundtripTest extends TestCase {
  private static $conn;
  private $fixture;

  /** @return void */
  public function setUp() {
    $this->fixture= (new RestClient('http://test'))->usingConnections([self::$conn, 'newInstance']);
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

  #[@test]
  public function default_headers() {
    $fixture= $this->fixture->with(['User-Agent' => 'Test']);
    $this->assertEquals(
      "GET / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "User-Agent: Test\r\n".
      "\r\n",
      $fixture->execute(new RestRequest('/', HttpConstants::GET))->content()
    );
  }

  #[@test]
  public function get() {
    $this->assertEquals(
      "GET / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->resource('/')->get()->content()
    );
  }

  #[@test]
  public function get_with_path() {
    $this->assertEquals(
      "GET /users HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->resource('/users')->get()->content()
    );
  }

  #[@test]
  public function get_with_segments() {
    $this->assertEquals(
      "GET /users/1 HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->resource('/users/{id}', ['id' => 1])->get()->content()
    );
  }

  #[@test]
  public function get_with_parameters() {
    $this->assertEquals(
      "GET /?page=1 HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->resource('/')->get(['page' => 1])->content()
    );
  }

  #[@test]
  public function get_with_header() {
    $this->assertEquals(
      "GET / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "User-Agent: Test\r\n".
      "\r\n",
      $this->fixture->resource('/')->with(['User-Agent' => 'Test'])->get()->content()
    );
  }

  #[@test]
  public function get_with_cookie() {
    $this->assertEquals(
      "GET / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Cookie: session=6100\r\n".
      "\r\n",
      $this->fixture->resource('/')->pass(['session' => '6100'])->get()->content()
    );
  }

  #[@test]
  public function get_with_accept() {
    $this->assertEquals(
      "GET / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Accept: */*\r\n".
      "\r\n",
      $this->fixture->resource('/')->accepting('*/*')->get()->content()
    );
  }

  #[@test]
  public function get_with_accept_with_q() {
    $this->assertEquals(
      "GET / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Accept: text/plain;q=1.0\r\n".
      "\r\n",
      $this->fixture->resource('/')->accepting('text/plain', '1.0')->get()->content()
    );
  }

  #[@test]
  public function head() {
    $this->assertEquals(
      "HEAD / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->resource('/')->head()->content()
    );
  }

  #[@test]
  public function post() {
    $this->assertEquals(
      "POST / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 15\r\n".
      "\r\n".
      "{\"name\":\"Test\"}",
      $this->fixture->resource('/')->post(['name' => 'Test'], 'application/json')->content()
    );
  }

  #[@test]
  public function post_using_json() {
    $this->assertEquals(
      "POST / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 15\r\n".
      "\r\n".
      "{\"name\":\"Test\"}",
      $this->fixture->resource('/')->using('application/json')->post(['name' => 'Test'])->content()
    );
  }

  #[@test]
  public function put() {
    $this->assertEquals(
      "PUT / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 15\r\n".
      "\r\n".
      "{\"name\":\"Test\"}",
      $this->fixture->resource('/')->put(['name' => 'Test'], 'application/json')->content()
    );
  }

  #[@test]
  public function patch() {
    $this->assertEquals(
      "PATCH / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "Content-Type: application/json\r\n".
      "Content-Length: 15\r\n".
      "\r\n".
      "{\"name\":\"Test\"}",
      $this->fixture->resource('/')->patch(['name' => 'Test'], 'application/json')->content()
    );
  }

  #[@test]
  public function delete() {
    $this->assertEquals(
      "DELETE / HTTP/1.1\r\n".
      "Connection: close\r\n".
      "Host: test\r\n".
      "\r\n",
      $this->fixture->resource('/')->delete()->content()
    );
  }
}
