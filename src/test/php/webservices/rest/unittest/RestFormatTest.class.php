<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestFormat;
use webservices\rest\RestXmlMap;
use webservices\rest\Payload;
use io\streams\MemoryInputStream;
use io\streams\MemoryOutputStream;
use lang\MapType;

/**
 * Test RestFormat class
 *
 * @see  xp://webservices.rest.RestFormat
 */
class RestFormatTest extends TestCase {

  #[@test]
  public function json_serialize() {
    $res= new MemoryOutputStream();
    RestFormat::$JSON->write($res, new Payload(['name' => 'Timm']));
    $this->assertEquals('{ "name" : "Timm" }', $res->getBytes());
  }

  #[@test]
  public function json_deserialize() {
    $req= new MemoryInputStream('{ "name" : "Timm" }');
    $v= RestFormat::$JSON->read($req, MapType::forName('[:string]'));
    $this->assertEquals(['name' => 'Timm'], $v); 
  }

  #[@test]
  public function xml_serialize() {
    $res= new MemoryOutputStream();
    RestFormat::$XML->write($res, new Payload(['name' => 'Timm']));
    $this->assertEquals(
      '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<root><name>Timm</name></root>', 
      $res->getBytes()
    );
  }

  #[@test]
  public function xml_deserialize() {
    $req= new MemoryInputStream('<?xml version="1.0" encoding="UTF-8"?>'."\n".'<root><name>Timm</name></root>');
    $v= RestFormat::$XML->read($req, MapType::forName('[:string]'));
    $this->assertEquals(['name' => 'Timm'], iterator_to_array($v));
  }

  #[@test]
  public function xml_deserialize_without_xml_declaration() {
    $req= new MemoryInputStream('<root><name>Timm</name></root>');
    $v= RestFormat::$XML->read($req, MapType::forName('[:string]'));
    $this->assertEquals(['name' => 'Timm'], iterator_to_array($v)); 
  }

  #[@test]
  public function form_deserialize() {
    $req= new MemoryInputStream('name=Timm');
    $v= RestFormat::$FORM->read($req, MapType::forName('[:string]'));
    $this->assertEquals(['name' => 'Timm'], $v);
  }

  #[@test]
  public function application_x_www_form_urlencoded_mediatype() {
    $this->assertEquals(RestFormat::$FORM, RestFormat::forMediaType('application/x-www-form-urlencoded'));
  }

  #[@test]
  public function text_json_mediatype() {
    $this->assertEquals(RestFormat::$JSON, RestFormat::forMediaType('text/json'));
  }

  #[@test]
  public function application_json_mediatype() {
    $this->assertEquals(RestFormat::$JSON, RestFormat::forMediaType('application/json'));
  }

  #[@test]
  public function text_xml_mediatype() {
    $this->assertEquals(RestFormat::$XML, RestFormat::forMediaType('text/xml'));
  }

  #[@test]
  public function application_xml_mediatype() {
    $this->assertEquals(RestFormat::$XML, RestFormat::forMediaType('application/xml'));
  }

  #[@test]
  public function application_octet_stream_mediatype() {
    $this->assertEquals(RestFormat::$UNKNOWN, RestFormat::forMediaType('application/octet-stream'));
  }
}
