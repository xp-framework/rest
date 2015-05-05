<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestRequest;
use webservices\rest\RestFormat;
use webservices\rest\RestJsonSerializer;
use webservices\rest\RestXmlSerializer;
use webservices\rest\Payload;
use peer\http\HttpConstants;
use peer\http\RequestData;
use peer\Header;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestRequest
 */
class RestRequestTest extends TestCase {
  
  #[@test]
  public function getResource() {
    $fixture= new RestRequest('/issues');
    $this->assertEquals('/issues', $fixture->getResource());
  }

  #[@test]
  public function setResource() {
    $fixture= new RestRequest();
    $fixture->setResource('/issues');
    $this->assertEquals('/issues', $fixture->getResource());
  }

  #[@test]
  public function withResource() {
    $fixture= new RestRequest();
    $this->assertEquals($fixture, $fixture->withResource('/issues'));
    $this->assertEquals('/issues', $fixture->getResource());
  }

  #[@test]
  public function getMethod() {
    $fixture= new RestRequest(null, HttpConstants::GET);
    $this->assertEquals(HttpConstants::GET, $fixture->getMethod());
  }

  #[@test]
  public function setMethod() {
    $fixture= new RestRequest();
    $fixture->setMethod(HttpConstants::GET);
    $this->assertEquals(HttpConstants::GET, $fixture->getMethod());
  }

  #[@test]
  public function withMethod() {
    $fixture= new RestRequest();
    $this->assertEquals($fixture, $fixture->withMethod(HttpConstants::GET));
    $this->assertEquals(HttpConstants::GET, $fixture->getMethod());
  }

  #[@test]
  public function setBody() {
    $request= new RequestData('{ "title" : "New issue" }');
    $fixture= new RestRequest();
    $fixture->setBody($request);
    $this->assertEquals($request, $fixture->getBody());
  }

  #[@test]
  public function withBody() {
    $request= new RequestData('{ "title" : "New issue" }');
    $fixture= new RestRequest();
    $this->assertEquals($fixture, $fixture->withBody($request));
    $this->assertEquals($request, $fixture->getBody());
  }

  #[@test]
  public function hasNoBody() {
    $fixture= new RestRequest();
    $this->assertFalse($fixture->hasBody());
  }

  #[@test]
  public function hasBody() {
    $fixture= new RestRequest();
    $fixture->setBody(new RequestData('{ "title" : "New issue" }'));
    $this->assertTrue($fixture->hasBody());
  }

  #[@test]
  public function hasPayloadWithJsonPayload() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], new RestJsonSerializer());
    $this->assertTrue($fixture->hasPayload());
  }

  #[@test]
  public function contentTypeWithJsonPayload() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], new RestJsonSerializer());
    $this->assertEquals('application/json; charset=utf-8', $fixture->getHeader('Content-Type'));
  }

  #[@test]
  public function getPayloadWithJsonPayload() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], new RestJsonSerializer());
    $this->assertEquals(['title' => 'New issue'], $fixture->getPayload());
  }

  #[@test]
  public function getPayloadWithJsonPayloadUsingRestFormat() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], RestFormat::$JSON);
    $this->assertEquals(['title' => 'New issue'], $fixture->getPayload());
  }

  #[@test]
  public function setPayloadAndNull() {
    $fixture= new RestRequest();
    $fixture->setPayload('Test', 'text/plain');
    $this->assertEquals('text/plain', $fixture->getContentType());
  }

  #[@test]
  public function withPayloadAndSerializer() {
    $fixture= new RestRequest();
    $this->assertEquals($fixture, $fixture->withPayload(null, new RestJsonSerializer()));
  }

  #[@test]
  public function withPayloadAndRestFormat() {
    $fixture= new RestRequest();
    $this->assertEquals($fixture, $fixture->withPayload(null, RestFormat::$JSON));
  }

  #[@test]
  public function withPayloadAndNull() {
    $fixture= (new RestRequest())->withPayload('Test', 'text/plain');
    $this->assertEquals('text/plain', $fixture->getContentType());
  }

  #[@test]
  public function hasPayloadWithXmlPayload() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], new RestXmlSerializer());
    $this->assertTrue($fixture->hasPayload());
  }

  #[@test]
  public function contentTypeWithXmlPayload() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], new RestXmlSerializer());
    $this->assertEquals('text/xml; charset=utf-8', $fixture->getHeader('Content-Type'));
  }

  #[@test]
  public function getPayloadWithXmlPayload() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], new RestXmlSerializer());
    $this->assertEquals(['title' => 'New issue'], $fixture->getPayload()); 
  }

  #[@test]
  public function getPayloadWithXmlPayloadAndRootNode() {
    $fixture= new RestRequest();
    $fixture->setPayload(new Payload(['title' => 'New issue'], ['name' => 'issue']), new RestXmlSerializer());
    $this->assertEquals(new Payload(['title' => 'New issue'], ['name' => 'issue']), $fixture->getPayload()); 
  }

  #[@test]
  public function setPayloadCalledTwice() {
    $fixture= new RestRequest();
    $fixture->setPayload(['title' => 'New issue'], new RestXmlSerializer());
    $fixture->setPayload(['title' => 'New issue'], new RestJsonSerializer());
    $this->assertEquals('application/json; charset=utf-8', $fixture->getHeader('Content-Type'));
  }

  #[@test]
  public function noParameters() {
    $fixture= new RestRequest();
    $this->assertEquals([], $fixture->getParameters());
  }

  #[@test]
  public function oneParameter() {
    $fixture= new RestRequest();
    $fixture->addParameter('filter', 'assigned');
    $this->assertEquals(
      ['filter' => 'assigned'],
      $fixture->getParameters()
    );
  }

  #[@test]
  public function twoParameters() {
    $fixture= new RestRequest('/issues');
    $fixture->addParameter('filter', 'assigned');
    $fixture->addParameter('state', 'open');
    $this->assertEquals(
      ['filter' => 'assigned', 'state' => 'open'],
      $fixture->getParameters()
    );
  }

  #[@test]
  public function noSegments() {
    $fixture= new RestRequest();
    $this->assertEquals([], $fixture->getSegments());
  }

  #[@test]
  public function oneSegment() {
    $fixture= new RestRequest();
    $fixture->addSegment('user', 'thekid');
    $this->assertEquals(
      ['user' => 'thekid'],
      $fixture->getSegments()
    );
  }

  #[@test]
  public function twoSegments() {
    $fixture= new RestRequest('/issues');
    $fixture->addSegment('user', 'thekid');
    $fixture->addSegment('repo', 'xp-framework');
    $this->assertEquals(
      ['user' => 'thekid', 'repo' => 'xp-framework'],
      $fixture->getSegments()
    );
  }

  #[@test]
  public function withSegmentReturnsThis() {
    $fixture= new RestRequest('/users/{user}');
    $this->assertEquals($fixture, $fixture->withSegment('user', 'thekid'));
  }

  #[@test]
  public function withParameterReturnsThis() {
    $fixture= new RestRequest('/issues');
    $this->assertEquals($fixture, $fixture->withParameter('filter', 'assigned'));
  }

  #[@test]
  public function targetWithoutParameters() {
    $fixture= new RestRequest('/issues');
    $this->assertEquals('/issues', $fixture->getTarget());
  }

  #[@test]
  public function targetWithSegmentParameter() {
    $fixture= new RestRequest('/users/{user}');
    $fixture->addSegment('user', 'thekid');
    $this->assertEquals('/users/thekid', $fixture->getTarget());
  }

  #[@test]
  public function targetWithTwoSegmentParameters() {
    $fixture= new RestRequest('/repos/{user}/{repo}');
    $fixture->addSegment('user', 'thekid');
    $fixture->addSegment('repo', 'xp-framework');
    $this->assertEquals('/repos/thekid/xp-framework', $fixture->getTarget());
  }

  #[@test]
  public function targetWithSegmentParametersAndConstantsMixed() {
    $fixture= new RestRequest('/repos/{user}/{repo}/issues/{id}');
    $fixture->addSegment('user', 'thekid');
    $fixture->addSegment('repo', 'xp-framework');
    $fixture->addSegment('id', 1);
    $this->assertEquals('/repos/thekid/xp-framework/issues/1', $fixture->getTarget());
  }

  #[@test, @values(['/rest/api/v2/', '/rest/api/v2'])]
  public function relativeResource($base) {
    $fixture= new RestRequest('issues');
    $this->assertEquals('/rest/api/v2/issues', $fixture->getTarget($base));
  }

  #[@test, @values(['/rest/api/v2/', '/rest/api/v2'])]
  public function absoluteResource($base) {
    $fixture= new RestRequest('/issues');
    $this->assertEquals('/rest/api/v2/issues', $fixture->getTarget($base));
  }

  #[@test]
  public function noHeaders() {
    $fixture= new RestRequest();
    $this->assertEquals([], $fixture->getHeaders());
  }

  #[@test]
  public function oneHeader() {
    $fixture= new RestRequest();
    $fixture->addHeader('Accept', 'text/xml');
    $this->assertEquals(
      ['Accept' => 'text/xml'],
      $fixture->getHeaders()
    );
  }

  #[@test]
  public function oneHeaderObject() {
    $fixture= new RestRequest();
    $fixture->addHeader(new Header('Accept', 'text/xml'));
    $this->assertEquals(
      ['Accept' => 'text/xml'],
      $fixture->getHeaders()
    );
  }

  #[@test]
  public function twoHeaders() {
    $fixture= new RestRequest('/issues');
    $fixture->addHeader('Accept', 'text/xml');
    $fixture->addHeader('Referer', 'http://localhost');
    $this->assertEquals(
      ['Accept' => 'text/xml', 'Referer' => 'http://localhost'],
      $fixture->getHeaders()
    );
  }

  #[@test]
  public function twoHeaderObjects() {
    $fixture= new RestRequest('/issues');
    $fixture->addHeader(new Header('Accept', 'text/xml'));
    $fixture->addHeader(new Header('Referer', 'http://localhost'));
    $this->assertEquals(
      ['Accept' => 'text/xml', 'Referer' => 'http://localhost'],
      $fixture->getHeaders()
    );
  }

  #[@test]
  public function headerListEmpty() {
    $fixture= new RestRequest('/issues');
    $this->assertEquals(
      [],
      $fixture->headerList()
    );
  }

  #[@test]
  public function headerListWithOneHeader() {
    $fixture= new RestRequest('/issues');
    $h= $fixture->addHeader(new Header('Accept', 'text/xml'));
    $this->assertEquals(
      [$h],
      $fixture->headerList()
    );
  }

  #[@test]
  public function addHeaderReturnsAddedHeaderObject() {
    $h= new Header('Accept', 'text/xml');
    $fixture= new RestRequest('/issues');
    $this->assertEquals($h, $fixture->addHeader($h));
  }

  #[@test]
  public function addHeaderReturnsAddedHeader() {
    $fixture= new RestRequest('/issues');
    $this->assertEquals(new Header('Accept', 'text/xml'), $fixture->addHeader('Accept', 'text/xml'));
  }

  #[@test]
  public function acceptOne() {
    $fixture= new RestRequest('/issues');
    $fixture->addAccept('text/xml');
    $this->assertEquals(
      ['Accept' => 'text/xml'],
      $fixture->getHeaders()
    );
  }

  #[@test]
  public function accept() {
    $fixture= new RestRequest('/issues');
    $fixture->addAccept('text/xml');
    $fixture->addAccept('text/json');
    $this->assertEquals(
      ['Accept' => 'text/xml, text/json'],
      $fixture->getHeaders()
    );
  }

  #[@test]
  public function acceptWithQ() {
    $fixture= new RestRequest('/issues');
    $fixture->addAccept('text/xml', '0.5');
    $fixture->addAccept('text/json', '0.8');
    $this->assertEquals(
      ['Accept' => 'text/xml;q=0.5, text/json;q=0.8'],
      $fixture->getHeaders()
    );
  }

  #[@test]
  public function stringRepresentation() {
    $this->assertEquals(
      "webservices.rest.RestRequest(GET /)@[\n".
      "]",
      (new RestRequest())->toString()
    );
  }

  #[@test]
  public function stringRepresentationWithUrl() {
    $this->assertEquals(
      "webservices.rest.RestRequest(GET /books)@[\n".
      "]",
      (new RestRequest('/books'))->toString()
    );
  }

  #[@test]
  public function stringRepresentationWithUrlAndMethod() {
    $this->assertEquals(
      "webservices.rest.RestRequest(POST /books)@[\n".
      "]",
      (new RestRequest('/books', 'POST'))->toString()
    );
  }

  #[@test]
  public function stringRepresentationWithHeader() {
    $this->assertEquals(
      "webservices.rest.RestRequest(GET /)@[\n".
      "  Referer: \"http://localhost\"\n".
      "]",
      (new RestRequest())->withHeader('Referer', 'http://localhost')->toString()
    );
  }

  #[@test]
  public function stringRepresentationWithAccept() {
    $this->assertEquals(
      "webservices.rest.RestRequest(GET /)@[\n".
      "  Accept: text/xml\n".
      "]",
      (new RestRequest())->withAccept('text/xml')->toString()
    );
  }

  #[@test]
  public function stringRepresentationWithHeaderAndAccept() {
    $this->assertEquals(
      "webservices.rest.RestRequest(GET /)@[\n".
      "  Referer: \"http://localhost\"\n".
      "  Accept: text/xml\n".
      "]",
      (new RestRequest())->withHeader('Referer', 'http://localhost')->withAccept('text/xml')->toString()
    );
  }
}