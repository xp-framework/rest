<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use peer\http\HttpConstants;
use io\streams\MemoryInputStream;
use webservices\rest\RestXmlDeserializer;
use webservices\rest\RestJsonDeserializer;
use webservices\rest\RestResponse;
use webservices\rest\ResponseReader;
use webservices\rest\RestMarshalling;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestResponse
 */
class RestResponseTest extends TestCase {
  const JSON = 'application/json';
  const XML  = 'text/xml';

  protected static $deserializers= [];

  /** @return void */
  #[@beforeClass]
  public static function deserializers() {
    self::$deserializers[self::JSON]= new RestJsonDeserializer();
    self::$deserializers[self::XML]= new RestXmlDeserializer();
  }

  /**
   * Creates a new fixture
   *
   * @param   string $content
   * @param   string $headers
   * @param   string $body
   * @return  webservices.rest.RestResponse
   */
  protected function newFixture($content, $headers, $body) {
    return new RestResponse(
      new \peer\http\HttpResponse(new MemoryInputStream(sprintf(
        "HTTP/1.1 200 OK\r\nContent-Type: %s\r\nContent-Length: %d%s\r\n\r\n%s",
        $content,
        strlen($body),
        $headers ? "\r\n".implode("\r\n", $headers) : '',
        $body
      ))),
      new ResponseReader(self::$deserializers[$content], new RestMarshalling()),
      \lang\Type::forName('[:var]')
    );
  }

  #[@test]
  public function content() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $this->assertEquals(
      '{ "issue_id" : 1, "title" : "test" }',
      $fixture->content()
    );
  }

  #[@test]
  public function headers() {
    $fixture= $this->newFixture(self::JSON, [], '');
    $this->assertEquals(
      ['Content-Type' => self::JSON, 'Content-Length' => '0'],
      $fixture->headers()
    );
  }

  #[@test]
  public function content_type_header() {
    $this->assertEquals(self::JSON, $this->newFixture(self::JSON, [], '')->header('Content-Type'));
  }

  #[@test]
  public function content_type_header_case_insensitive() {
    $this->assertEquals(self::JSON, $this->newFixture(self::JSON, [], '')->header('content-type'));
  }

  #[@test]
  public function non_existant_header() {
    $this->assertNull($this->newFixture(self::JSON, [], '')->header('@@non-existant@@'));
  }

  #[@test]
  public function without_cookies() {
    $this->assertEquals([], $this->newFixture(self::JSON, [], '')->cookies());
  }

  #[@test]
  public function with_cookies() {
    $headers= [
      'Set-Cookie: one=1',
      'Set-Cookie: two=2; httpOnly'
    ];
    $this->assertEquals(
      ['one' => '1', 'two' => '2; httpOnly'],
      $this->newFixture(self::JSON, $headers, '')->cookies()
    );
  }

  #[@test]
  public function dataAsMap() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $this->assertEquals(
      ['issue_id' => 1, 'title' => 'test'],
      $fixture->data()
    );
  }

  #[@test]
  public function dataAsMapWithNull() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : null }');
    $this->assertEquals(
      ['issue_id' => 1, 'title' => null], 
      $fixture->data()
    );
  }

  #[@test]
  public function dataAsTypeWithField() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $this->assertEquals(
      new IssueWithField(1, 'test'), 
      $fixture->data(\lang\XPClass::forName('webservices.rest.unittest.IssueWithField'))
    );
  }

  #[@test]
  public function dataAsTypeWithUnderscoreField() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $this->assertEquals(
      new IssueWithUnderscoreField(1, 'test'), 
      $fixture->data(\lang\XPClass::forName('webservices.rest.unittest.IssueWithUnderscoreField'))
    );
  }

  #[@test]
  public function dataAsTypeWithSetter() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $this->assertEquals(
      new IssueWithSetter(1, 'test'), 
      $fixture->data(\lang\XPClass::forName('webservices.rest.unittest.IssueWithSetter'))
    );
  }

  #[@test]
  public function dataAsTypeWithUnderscoreSetter() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $this->assertEquals(
      new IssueWithUnderscoreSetter(1, 'test'), 
      $fixture->data(\lang\XPClass::forName('webservices.rest.unittest.IssueWithUnderscoreSetter'))
    );
  }

  #[@test]
  public function dataAsTypeByName() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $this->assertEquals(
      new IssueWithField(1, 'test'), 
      $fixture->data('webservices.rest.unittest.IssueWithField')
    );
  }

  #[@test]
  public function dataAsTypeByNameWithNull() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : null }');
    $this->assertEquals(
      new IssueWithField(1, null), 
      $fixture->data('webservices.rest.unittest.IssueWithField')
    );
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function dataAsNonExistantType() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issue_id" : 1, "title" : "test" }');
    $fixture->data('non.existant.Type');
  }

  #[@test]
  public function typedArrayData() {
    $fixture= $this->newFixture(self::JSON, [], '[ { "issue_id" : 1, "title" : "Found a bug" }, { "issue_id" : 2, "title" : "Another" } ]');
    $list= $fixture->data(\lang\Type::forName('webservices.rest.unittest.IssueWithField[]'));
    $this->assertEquals(new IssueWithField(1, 'Found a bug'), $list[0]);
    $this->assertEquals(new IssueWithField(2, 'Another'), $list[1]);
  }

  #[@test]
  public function nestedDataAsTypeWithSetter() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issues" : [ { "issue_id" : 1, "title" : "Found a bug" }, { "issue_id" : 2, "title" : "Another" } ] }');
    $list= $fixture->data(\lang\Type::forName('webservices.rest.unittest.IssuesWithSetter'));

    $this->assertEquals(
      new IssuesWithSetter([
        new IssueWithField(1, 'Found a bug'),
        new IssueWithField(2, 'Another')
      ]),
      $list
    );
  }

  #[@test]
  public function nestedDataAsTypeWithField() {
    $fixture= $this->newFixture(self::JSON, [], '{ "issues" : [ { "issue_id" : 1, "title" : "Found a bug" }, { "issue_id" : 2, "title" : "Another" } ] }');
    $list= $fixture->data(\lang\Type::forName('webservices.rest.unittest.IssuesWithField'));

    $this->assertEquals(
      new IssuesWithField([
        new IssueWithField(1, 'Found a bug'),
        new IssueWithField(2, 'Another')
      ]),
      $list
    );
  }

  #[@test]
  public function xmlAsMap() {
    $fixture= $this->newFixture(self::XML, [], '<issue><issue_id>1</issue_id><title/></issue>');
    $this->assertEquals(
      ['issue_id' => '1', 'title' => ''], 
      $fixture->data()
    );
  }

  #[@test]
  public function nestedXmlAsMap() {
    $fixture= $this->newFixture(self::XML, [], '<book><author><id>1549</id><name>Timm</name></author></book>');
    $this->assertEquals(
      ['author' => ['id' => '1549', 'name' => 'Timm']],
      $fixture->data()
    );
  }

  #[@test]
  public function nestedXmlAsTypeWithSetter() {
    $fixture= $this->newFixture(self::XML, [], '<object>
      <issues>
        <issue><issue_id>1</issue_id><title>Found a bug</title></issue>
        <issue><issue_id>2</issue_id><title>Another</title></issue>
      </issues>
    </object>');
    $list= $fixture->data(\lang\Type::forName('webservices.rest.unittest.IssuesWithSetter'));

    $this->assertEquals(
      new IssuesWithSetter([
        new IssueWithField(1, 'Found a bug'),
        new IssueWithField(2, 'Another')
      ]),
      $list
    );
  }

  #[@test]
  public function nestedXmlAsTypeWithField() {
    $fixture= $this->newFixture(self::XML, [], '<object>
      <issues>
        <issue><issue_id>1</issue_id><title>Found a bug</title></issue>
        <issue><issue_id>2</issue_id><title>Another</title></issue>
      </issues>
    </object>');
    $list= $fixture->data(\lang\Type::forName('webservices.rest.unittest.IssuesWithField'));

    $this->assertEquals(
      new IssuesWithField([
        new IssueWithField(1, 'Found a bug'),
        new IssueWithField(2, 'Another')
      ]),
      $list
    );
  }

  #[@test]
  public function stringRepresentation() {
    $this->assertEquals(
      "webservices.rest.RestResponse<OK>@(->peer.http.HttpResponse (HTTP/1.1 200 OK) {\n".
      "  [Content-Type        ] { application/json }\n".
      "  [Content-Length      ] { 9 }\n".
      "})",
      $this->newFixture(self::JSON, [], '"payload"')->toString()
    );
  }
}
