<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestClient;
use webservices\rest\CannotSerialize;
use webservices\rest\CannotDeserialize;
use peer\http\HttpConnection;
use peer\URL;
use lang\IllegalArgumentException;
use lang\IllegalStateException;
use lang\Error;
use unittest\actions\RuntimeVersion;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestClient
 */
class RestClientTest extends TestCase {
  const BASE_URL = 'http://example.com';

  /**
   * Creates a new RestClient fixture with a given base
   *
   * @param   var base
   * @return  webservices.rest.RestClient
   */
  protected function newFixture($base= null) {
    return new RestClient($base);
  }

  #[@test]
  public function stringBase() {
    $this->assertEquals(
      new URL(self::BASE_URL),
      $this->newFixture(self::BASE_URL)->getBase()
    );
  }

  #[@test]
  public function nullBase() {
    $this->assertNull($this->newFixture()->getBase());
  }

  #[@test]
  public function urlBase() {
    $this->assertEquals(
      new URL(self::BASE_URL),
      $this->newFixture(new URL(self::BASE_URL))->getBase()
    );
  }

  #[@test]
  public function setBase() {
    $fixture= $this->newFixture();
    $fixture->setBase(self::BASE_URL);
    $this->assertEquals(new URL(self::BASE_URL), $fixture->getBase());
  }

  #[@test]
  public function withBase() {
    $fixture= $this->newFixture();
    $this->assertEquals($fixture, $fixture->withBase(self::BASE_URL));
    $this->assertEquals(new URL(self::BASE_URL), $fixture->getBase());
  }

  #[@test]
  public function setConnection() {
    $fixture= $this->newFixture();
    $fixture->setConnection(new HttpConnection(self::BASE_URL));
    $this->assertEquals(new URL(self::BASE_URL), $fixture->getBase());
  }

  #[@test, @action(new RuntimeVersion('>=7.0.0')), @expect(Error::class)]
  public function illegal_argument7() {
    $this->newFixture()->execute(null);
  }

  #[@test, @action(new RuntimeVersion('<7.0.0')), @expect(IllegalArgumentException::class)]
  public function illegal_argument() {
    $this->newFixture()->execute(null);
  }

  #[@test, @expect(class= IllegalStateException::class, withMessage= 'No connection set')]
  public function executeWithoutBase() {
    $this->newFixture()->execute(new \webservices\rest\RestRequest());
  }

  #[@test]
  public function textXmlDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/xml')
    );
  }

  #[@test]
  public function applicationXmlDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('application/xml')
    );
  }

  #[@test]
  public function textJsonDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/json')
    );
  }

  #[@test]
  public function textXJsonDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/x-json')
    );
  }

  #[@test]
  public function textJavascriptDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('text/javascript')
    );
  }

  #[@test]
  public function applicationJsonDeserializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestDeserializer',
      $this->newFixture()->deserializerFor('application/json')
    );
  }

  #[@test]
  public function unknownDeserializer() {
    $this->assertEquals(new CannotDeserialize('text/html'), $this->newFixture()->deserializerFor('text/html'));
  }

  #[@test]
  public function textXmlSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/xml')
    );
  }

  #[@test]
  public function applicationXmlSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('application/xml')
    );
  }

  #[@test]
  public function textJsonSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/json')
    );
  }

  #[@test]
  public function textXJsonSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/x-json')
    );
  }

  #[@test]
  public function textJavascriptSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('text/javascript')
    );
  }

  #[@test]
  public function applicationJsonSerializer() {
    $this->assertInstanceOf(
      'webservices.rest.RestSerializer',
      $this->newFixture()->serializerFor('application/json')
    );
  }

  #[@test]
  public function unknownSerializer() {
    $this->assertEquals(new CannotSerialize('text/html'), $this->newFixture()->serializerFor('text/html'));
  }

  #[@test]
  public function stringRepresentation() {
    $this->assertEquals(
      "webservices.rest.RestClient(->null)",
      $this->newFixture()->toString()
    );
  }

  #[@test]
  public function stringRepresentationWithBase() {
    $this->assertEquals(
      "webservices.rest.RestClient(->peer.http.HttpConnection(->URL{http://api.example.com/ via peer.http.SocketHttpTransport}, timeout: [read= 60.00, connect= 2.00]))",
      $this->newFixture('http://api.example.com/')->toString()
    );
  }

  #[@test]
  public function setConnectTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');
    $fixture->setConnectTimeout(31337);

    $this->assertEquals(31337, $fixture->getConnectTimeout());
  }

  #[@test]
  public function setTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');
    $fixture->setTimeout(31337);

    $this->assertEquals(31337, $fixture->getTimeout());
  }

  #[@test]
  public function inheritsAConnectionsDefaultConnectTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');

    $this->assertEquals(2.0, $fixture->getConnectTimeout());
  }

  #[@test]
  public function inheritsAConnectionsDefaultTimeout() {
    $fixture= $this->newFixture();
    $fixture->setBase('http://localhost/');

    $this->assertEquals(60, $fixture->getTimeout());
  }
}
