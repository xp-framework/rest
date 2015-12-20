<?php namespace webservices\rest\unittest\srv;

use io\File;
use io\collections\IOElement;
use unittest\TestCase;
use webservices\rest\srv\StreamingOutput;
use io\streams\MemoryInputStream;

/**
 * Test response class
 *
 * @see  xp://webservices.rest.srv.StreamingOutput
 */
class StreamingOutputTest extends TestCase {

  #[@test]
  public function no_input_stream() {
    $this->assertEquals(null, (new StreamingOutput())->inputStream);
  }

  #[@test]
  public function input_stream_given() {
    $s= new MemoryInputStream('Test');
    $this->assertEquals($s, (new StreamingOutput($s))->inputStream);
  }

  #[@test]
  public function of_with_input_stream() {
    $s= new MemoryInputStream('Test');
    $this->assertEquals(
      (new StreamingOutput($s))
        ->withMediaType('application/octet-stream')
        ->withContentLength(null)
        ->withLastModified(null)
      ,
      StreamingOutput::of($s)
    );
  }

  #[@test]
  public function of_with_file() {
    $f= newinstance(File::class, [new MemoryInputStream('Test')], '{
      protected $stream;
      public function __construct($stream) { $this->stream= $stream; }
      public function getFileName() { return "test.txt"; }
      public function getSize() { return 6100; }
      public function getInputStream() { return $this->stream; }
      public function lastModified() { return 1364291580; }
    }');
    $this->assertEquals(
      (new StreamingOutput($f->getInputStream()))
        ->withMediaType('text/plain')
        ->withContentLength(6100)
        ->withLastModified(new \util\Date('2013-03-26 10:53:00'))
      ,
      StreamingOutput::of($f)
    );
  }

  #[@test]
  public function of_with_io_element() {
    $e= newinstance(IOElement::class, [new MemoryInputStream('Test')], '{
      protected $stream;
      public function __construct($stream) { $this->stream= $stream; }
      public function getName() { return "test.txt"; }
      public function getURI() { return "/path/to/test.txt"; }
      public function getSize() { return 6100; }
      public function createdAt() { return null; }
      public function lastAccessed() { return null; }
      public function lastModified() { return new \util\Date("2013-03-26 10:53:00"); }
      public function getOrigin() { return null; }
      public function setOrigin(IOCollection $origin) { }
      public function getInputStream() { return $this->stream; }
      public function getOutputStream() { return null; }
    }');
    $this->assertEquals(
      (new StreamingOutput($e->getInputStream()))
        ->withMediaType('text/plain')
        ->withContentLength(6100)
        ->withLastModified(new \util\Date('2013-03-26 10:53:00'))
      ,
      StreamingOutput::of($e)
    );
  }

  #[@test]
  public function default_status_code_is_200() {
    $this->assertEquals(200, (new StreamingOutput())->status);
  }

  #[@test]
  public function status_code_can_be_changed() {
    $this->assertEquals(304, (new StreamingOutput())->withStatus(304)->status);
  }
}
