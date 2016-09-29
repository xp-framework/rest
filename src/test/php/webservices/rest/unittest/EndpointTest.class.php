<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\Endpoint;
use webservices\rest\CannotSerialize;
use webservices\rest\CannotDeserialize;
use webservices\rest\RestDeserializer;
use webservices\rest\RestSerializer;
use peer\http\HttpConnection;
use peer\URL;
use lang\IllegalArgumentException;
use lang\IllegalStateException;
use lang\FormatException;
use lang\Error;
use unittest\actions\RuntimeVersion;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.Endpoint
 */
class EndpointTest extends TestCase {
  const BASE_URL = 'http://example.com';

  /**
   * Creates a new Endpoint fixture with a given base
   *
   * @param  string|peer.URL $base
   * @return webservices.rest.Endpoint
   */
  protected function newFixture($base= self::BASE_URL) {
    return new Endpoint($base);
  }

  #[@test, @values([self::BASE_URL, new URL(self::BASE_URL)])]
  public function can_create($base) {
    $this->newFixture($base);
  }

  #[@test, @values([null, '']), @expect(FormatException::class)]
  public function cannot_create_with_illegal_url($base) {
    $this->newFixture($base);
  }

  #[@test, @values([self::BASE_URL, new URL(self::BASE_URL)])]
  public function base_url($base) {
    $this->assertEquals(new URL(self::BASE_URL), $this->newFixture($base)->baseUrl());
  }

  #[@test, @action(new RuntimeVersion('>=7.0.0')), @expect(Error::class)]
  public function execute_given_illegal_argument7() {
    $this->newFixture()->execute(null);
  }

  #[@test, @action(new RuntimeVersion('<7.0.0')), @expect(IllegalArgumentException::class)]
  public function execute_given_illegal_argument() {
    $this->newFixture()->execute(null);
  }

  #[@test, @values([
  #  'text/xml',
  #  'application/xml'
  #])]
  public function xml_serializer_builtin($mimeType) {
    $this->assertInstanceOf(RestSerializer::class, $this->newFixture()->serializerFor($mimeType));
  }

  #[@test, @values([
  #  'text/xml',
  #  'application/xml'
  #])]
  public function xml_deserializer_builtin($mimeType) {
    $this->assertInstanceOf(RestDeserializer::class, $this->newFixture()->deserializerFor($mimeType));
  }

  #[@test, @values([
  #  'text/json',
  #  'text/x-json',
  #  'text/javascript',
  #  'application/json'
  #])]
  public function json_serializer_builtin($mimeType) {
    $this->assertInstanceOf(RestSerializer::class, $this->newFixture()->serializerFor($mimeType));
  }

  #[@test, @values([
  #  'text/json',
  #  'text/x-json',
  #  'text/javascript',
  #  'application/json'
  #])]
  public function json_deserializer_builtin($mimeType) {
    $this->assertInstanceOf(RestDeserializer::class, $this->newFixture()->deserializerFor($mimeType));
  }

  #[@test]
  public function form_serializer_builtin() {
    $this->assertInstanceOf(RestSerializer::class, $this->newFixture()->serializerFor('application/x-www-urlencoded'));
  }

  #[@test]
  public function unknown_serializer() {
    $this->assertEquals(
      new CannotSerialize('text/html'),
      $this->newFixture()->serializerFor('text/html')
    );
  }

  #[@test]
  public function unknown_deserializer() {
    $this->assertEquals(
      new CannotDeserialize('text/html'),
      $this->newFixture()->deserializerFor('text/html')
    );
  }

  #[@test]
  public function string_representation() {
    $this->assertEquals(
      'webservices.rest.Endpoint(->http://api.example.com/, timeouts: [read= 60.00, connect= 2.00])@[]',
      $this->newFixture('http://api.example.com/')->toString()
    );
  }

  #[@test]
  public function headers() {
    $this->assertEquals(
      [],
      $this->newFixture()->headers()
    );
  }

  #[@test]
  public function with_headers() {
    $this->assertEquals(
      ['User-Agent' => 'Test'],
      $this->newFixture()->with(['User-Agent' => 'Test'])->headers()
    );
  }

  #[@test]
  public function with_header() {
    $this->assertEquals(
      ['User-Agent' => 'Test'],
      $this->newFixture()->with('User-Agent', 'Test')->headers()
    );
  }

  #[@test]
  public function with_headers_invoked_multiple_times() {
    $this->assertEquals(
      ['User-Agent' => 'Test', 'Token' => 'AA8F'],
      $this->newFixture()->with(['User-Agent' => 'Test'])->with(['Token' => 'AA8F'])->headers()
    );
  }

  #[@test]
  public function with_header_invoked_multiple_times() {
    $this->assertEquals(
      ['User-Agent' => 'Test', 'Token' => 'AA8F'],
      $this->newFixture()->with('User-Agent', 'Test')->with('Token', 'AA8F')->headers()
    );
  }

  #[@test]
  public function default_timeouts() {
    $this->assertEquals(
      ['read' => 60.0, 'connect' => 2.0],
      $this->newFixture()->timeouts()
    );
  }

  #[@test]
  public function timeouts() {
    $this->assertEquals(
      ['read' => 30.0, 'connect' => 4.0],
      $this->newFixture()->usingTimeouts(30.0, 4.0)->timeouts()
    );
  }
}