<?php namespace webservices\rest\unittest\srv;

use webservices\rest\srv\Response;
use webservices\rest\Payload;
use scriptlet\Cookie;
use scriptlet\HttpScriptletResponse;
use peer\URL;
use webservices\rest\unittest\srv\fixture\Greeting;

/**
 * Test response class
 *
 * @see  xp://webservices.rest.srv.Response
 */
class ResponseTest extends \unittest\TestCase {

  #[@test]
  public function create() {
    $this->assertEquals(null, (new Response())->status);
  }

  #[@test]
  public function create_with_status() {
    $this->assertEquals(200, (new Response(200))->status);
  }

  #[@test]
  public function payload_initially_null() {
    $this->assertNull((new Response())->payload);
  }

  #[@test]
  public function headers_initially_empty() {
    $this->assertEquals([], (new Response())->headers);
  }

  #[@test]
  public function ok() {
    $r= Response::ok();
    $this->assertEquals(200, $r->status);
  }

  #[@test]
  public function created() {
    $r= Response::created();
    $this->assertEquals(201, $r->status);
    $this->assertEquals([], $r->headers);
  }

  #[@test]
  public function created_with_location() {
    $location= 'http://example.com/resource/4711';
    $r= Response::created($location);
    $this->assertEquals(201, $r->status);
    $this->assertEquals(['Location' => $location], $r->headers);
  }

  #[@test]
  public function no_content() {
    $r= Response::noContent();
    $this->assertEquals(204, $r->status);
  }

  #[@test]
  public function see() {
    $location= 'http://example.com/resource/4711';
    $r= Response::see($location);
    $this->assertEquals(302, $r->status);
  }

  #[@test]
  public function not_modified() {
    $r= Response::notModified();
    $this->assertEquals(304, $r->status);
  }

  #[@test]
  public function not_found() {
    $r= Response::notFound();
    $this->assertEquals(404, $r->status);
  }

  #[@test]
  public function not_acceptable() {
    $r= Response::notAcceptable();
    $this->assertEquals(406, $r->status);
  }

  #[@test]
  public function error() {
    $r= Response::error();
    $this->assertEquals(500, $r->status);
  }

  #[@test]
  public function error_503() {
    $r= Response::error(503);
    $this->assertEquals(503, $r->status);
  }

  #[@test]
  public function status_402() {
    $r= Response::status(402);
    $this->assertEquals(402, $r->status);
  }

  #[@test]
  public function not_found_with_message() {
    $r= Response::notFound('No file named four-oh-four');
    $this->assertEquals(new Payload('No file named four-oh-four'), $r->payload);
  }

  #[@test]
  public function not_acceptable_with_message() {
    $r= Response::notAcceptable('Cannot upload files named four-our-six');
    $this->assertEquals(new Payload('Cannot upload files named four-our-six'), $r->payload);
  }

  #[@test]
  public function error_with_message() {
    $r= Response::error(503, 'Come back later');
    $this->assertEquals(new Payload('Come back later'), $r->payload);
  }

  #[@test]
  public function status_with_message() {
    $r= Response::status(203, 'Eventually consistent');
    $this->assertEquals(new Payload('Eventually consistent'), $r->payload);
  }

  #[@test]
  public function with_extra_header() {
    $r= (new Response())->withHeader('X-Exception', 'SQL');
    $this->assertEquals(['X-Exception' => 'SQL'], $r->headers);
  }

  #[@test]
  public function with_payload() {
    $data= ['name' => 'example'];
    $r= (new Response())->withPayload($data);
    $this->assertEquals(new Payload($data), $r->payload);
  }

  #[@test]
  public function with_payload_instance() {
    $data= ['name' => 'example'];
    $r= (new Response())->withPayload(new Payload($data));
    $this->assertEquals(new Payload($data), $r->payload);
  }

  #[@test]
  public function equals_identical() {
    $r= Response::status(200);
    $this->assertEquals($r, $r);
  }

  #[@test]
  public function equals_same() {
    $this->assertEquals(Response::status(200), Response::status(200));
  }

  #[@test]
  public function equals_with_headers() {
    $this->assertEquals(
      Response::status(200)->withHeader('ETag', '4711'), 
      Response::status(200)->withHeader('ETag', '4711')
    );
  }

  #[@test]
  public function equals_different_status() {
    $this->assertNotEquals(Response::status(200), Response::status(201));
  }

  #[@test]
  public function equals_different_header_values() {
    $this->assertNotEquals(
      Response::status(200)->withHeader('ETag', '4711'), 
      Response::status(200)->withHeader('ETag', '4712')
    );
  }

  #[@test]
  public function equals_different_header_keys() {
    $this->assertNotEquals(
      Response::status(200)->withHeader('ETag', '4711'), 
      Response::status(200)->withHeader('X-Any-Number', '4711')
    );
  }

  #[@test]
  public function equals_different_header_sizes() {
    $this->assertNotEquals(
      Response::status(200), 
      Response::status(200)->withHeader('X-Any-Number', '4711')
    );
  }

  #[@test]
  public function equals_same_primitive_payloads() {
    $this->assertEquals(
      Response::status(200)->withPayload('4711'), 
      Response::status(200)->withPayload('4711')
    );
  }

  #[@test]
  public function equals_different_primitive_payloads() {
    $this->assertNotEquals(
      Response::status(200)->withPayload('4711'), 
      Response::status(200)->withPayload('4712')
    );
  }

  #[@test]
  public function equals_same_array_payloads() {
    $this->assertEquals(
      Response::status(200)->withPayload(['4711', 4712, null]), 
      Response::status(200)->withPayload(['4711', 4712, null])
    );
  }

  #[@test]
  public function equals_different_array_payloads() {
    $this->assertNotEquals(
      Response::status(200)->withPayload(['4711', 4712, null]), 
      Response::status(200)->withPayload(['4711', 4713, null])
    );
  }

  #[@test]
  public function equals_identical_object_payloads() {
    $this->assertEquals(
      Response::status(200)->withPayload($this), 
      Response::status(200)->withPayload($this)
    );
  }

  #[@test]
  public function equals_same_object_payloads() {
    $this->assertEquals(
      Response::status(200)->withPayload(new Greeting('Hello', 'World')), 
      Response::status(200)->withPayload(new Greeting('Hello', 'World'))
    );
  }

  #[@test]
  public function equals_different_object_payloads() {
    $this->assertNotEquals(
      Response::status(200)->withPayload($this), 
      Response::status(200)->withPayload(new Greeting('Hello', 'World'))
    );
  }

  #[@test]
  public function equals_object_and_null_payloads() {
    $this->assertNotEquals(
      Response::status(200)->withPayload($this), 
      Response::status(200)->withPayload(null)
    );
  }

  #[@test]
  public function equals_null() {
    $this->assertEquals(
      Response::status(200)->withPayload(null), 
      Response::status(200)->withPayload(null)
    );
  }

  #[@test]
  public function without_cookies() {
    $this->assertEquals(
      [],
      Response::status(200)->cookies 
    );
  }

  #[@test]
  public function with_one_cookie() {
    $user= new Cookie('user', 'Test');
    $this->assertEquals(
      [$user],
      Response::status(200)->withCookie($user)->cookies 
    );
  }

  #[@test]
  public function with_two_cookies() {
    $user= new Cookie('user', 'Test');
    $lang= new Cookie('language', 'de');
    $this->assertEquals(
      [$user, $lang],
      Response::status(200)->withCookie($user)->withCookie($lang)->cookies 
    );
  }

  #[@test]
  public function writeTo_fully_qualifies_path_in_location_header() {
    $res= new HttpScriptletResponse();
    Response::see('/foo')->writeTo($res, new URL('http://example.com/'), null);
    $this->assertEquals('Location: http://example.com/foo', $res->headers[0]);
  }

  #[@test]
  public function writeTo_does_not_fully_qualify_url_in_location_header() {
    $res= new HttpScriptletResponse();
    Response::see('http://localhost/')->writeTo($res, new URL('http://example.com/'), null);
    $this->assertEquals('Location: http://localhost/', $res->headers[0]);
  }
}
