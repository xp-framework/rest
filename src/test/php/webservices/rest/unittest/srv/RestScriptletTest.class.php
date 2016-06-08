<?php namespace webservices\rest\unittest\srv;

use unittest\TestCase;
use webservices\rest\srv\RestScriptlet;
use webservices\rest\srv\RestDefaultRouter;
use webservices\rest\srv\RestContext;
use scriptlet\HttpScriptletRequest;
use scriptlet\HttpScriptletResponse;
use peer\URL;

/**
 * Test response class
 *
 * @see  xp://webservices.rest.srv.RestScriptlet
 * @see  https://github.com/xp-framework/xp-framework/issues/258
 * @see  https://github.com/xp-framework/xp-framework/issues/319
 * @see  http://www.w3.org/Protocols/rfc2616/rfc2616-sec7.html#sec7.2.1
 */
class RestScriptletTest extends TestCase {

  /**
   * Creates a new fixture
   *
   * @return webservices.rest.srv.RestScriptlet
   */
  protected function newFixture() {
    return new RestScriptlet('webservices.rest.unittest.srv.fixture');
  }

  #[@test]
  public function can_create() {
    new RestScriptlet('webservices.rest.unittest.srv.fixture');
  }

  #[@test]
  public function can_create_with_custom_router() {
    new RestScriptlet('webservices.rest.unittest.srv.fixture', '', '', 'webservices.rest.srv.RestDefaultRouter');
  }

  #[@test]
  public function router_accessors() {
    $fixture= $this->newFixture();
    $router= new RestDefaultRouter();
    $fixture->setRouter($router);
    $this->assertEquals($router, $fixture->getRouter());
  }

  #[@test]
  public function context_accessors() {
    $fixture= $this->newFixture();
    $context= new RestContext();
    $fixture->setContext($context);
    $this->assertEquals($context, $fixture->getContext());
  }

  #[@test]
  public function cannot_route() {
    $fixture= $this->newFixture();
    $req= new HttpScriptletRequest();
    $req->setURI(new URL('http://localhost/'));
    $res= new HttpScriptletResponse();
    $fixture->doProcess($req, $res);

    ob_start();
    $res->sendContent();
    $sent= ob_get_contents();
    ob_end_clean();

    $this->assertEquals(404, $res->statusCode);
    $this->assertEquals('Content-Type: application/json', $res->headers[0]);
    $this->assertEquals('{"message":"Could not route request to http://localhost/"}', $sent);
  }

  #[@test, @values([
  #  [['Content-Type' => 'application/json']],
  #  [[]]
  #])]
  public function contentTypeOf_request_without_content_length_or_te_is_null($headers) {
    $req= new HttpScriptletRequest();
    $req->setHeaders($headers);
    $this->assertEquals(null, $this->newFixture()->contentTypeOf($req));
  }

  #[@test]
  public function contentTypeOf_request_with_content_length() {
    $req= new HttpScriptletRequest();
    $req->setHeaders(['Content-Type' => 'application/json', 'Content-Length' => 6100]);
    $this->assertEquals('application/json', $this->newFixture()->contentTypeOf($req));
  }

  #[@test]
  public function contentTypeOf_request_with_transfer_encoding() {
    $req= new HttpScriptletRequest();
    $req->setHeaders(['Content-Type' => 'application/json', 'Transfer-Encoding' => 'chunked']);
    $this->assertEquals('application/json', $this->newFixture()->contentTypeOf($req));
  }

  #[@test, @values([
  #  [['Content-Length' => 6100]],
  #  [['Transfer-Encoding' => 'chunked']]
  #])]
  public function default_contentTypeOf_request_with_body() {
    $req= new HttpScriptletRequest();
    $req->setHeaders(['Transfer-Encoding' => 'chunked']);
    $this->assertEquals('application/octet-stream', $this->newFixture()->contentTypeOf($req));
  }
}
