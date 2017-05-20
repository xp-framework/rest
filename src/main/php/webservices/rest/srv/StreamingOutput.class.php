<?php namespace webservices\rest\srv;

use util\MimeType;
use util\Date;
use util\Objects;
use lang\IllegalArgumentException;
use io\File;
use io\streams\InputStream;
use io\collections\IOElement;

/**
 * Represents a stream to be output
 *
 */
class StreamingOutput extends Output {
  public $inputStream= null;
  public $mediaType= 'application/octet-stream';
  public $contentLength= null;
  public $lastModified= null;
  public $payload= null;
  public $buffered= false;

  /**
   * Creates a new streaming output instance with a given input stream
   *
   * @param  io.streams.InputStream inputStream
   */
  public function __construct(InputStream $inputStream= null) {
    $this->inputStream= $inputStream;
    $this->status= 200;
  }

  /**
   * Factory method
   *
   * @param  var arg either an InputStream, File, or IOElement
   * @return self
   * @throws lang.IllegalArgumentException
   */
  public static function of($arg) {
    if ($arg instanceof InputStream) {
      return new self($arg);
    } else if ($arg instanceof File) {
      return (new self($arg->in()))
        ->withMediaType(MimeType::getByFileName($arg->getFileName()))
        ->withContentLength($arg->getSize())
        ->withLastModified(new Date($arg->lastModified()))
      ;
    } else if ($arg instanceof IOElement) {
      return (new self($arg->getInputStream()))
        ->withMediaType(MimeType::getByFileName($arg->getURI()))
        ->withContentLength($arg->getSize())
        ->withLastModified($arg->lastModified())
      ;
    } else {
      throw new IllegalArgumentException('Expected either an InputStream, File, or IOElement, have '.typeof($arg)->getName());
    }
  }

  /**
   * Sets statuscode
   *
   * @param  int status HTTP status code
   * @return self
   */
  public function withStatus($status) {
    $this->status= $status;
    return $this;
  }

  /**
   * Sets contentLength. Pass NULL to avoid setting the "Content-Length" header.
   *
   * @param  int length
   * @return self
   */
  public function withContentLength($length) {
    $this->contentLength= $length;
    return $this;
  }

  /**
   * Sets mediaType
   *
   * @param  string type
   * @return self
   */
  public function withMediaType($type) {
    $this->mediaType= $type;
    return $this;
  }

  /**
   * Sets lastModified
   *
   * @param  util.Date date
   * @return self
   */
  public function withLastModified(Date $date= null) {
    $this->lastModified= $date;
    return $this;
  }

  /**
   * Writes to output. This default implementation will copy data from
   * the input stream while data is available on it.
   *
   * @param  io.streams.OutputStream out
   */
  public function write($out) {
    while ($this->inputStream->available()) {
      $out->write($this->inputStream->read());
    }
  }

  /**
   * Write response headers
   *
   * @param  scriptlet.Response response
   * @param  peer.URL base
   * @param  string format
   */
  protected function writeHead($response, $base, $format) {
    $response->setContentType($this->mediaType);
    if (null !== $this->contentLength) {
      $response->setContentLength($this->contentLength);
    }
    if (null !== $this->lastModified) {
      $response->setHeader('Last-Modified', \util\TimeZone::getByName('GMT')
        ->translate($this->lastModified)
        ->toString('D, d M Y H:i:s \G\M\T')
      );
    }
  }

  /**
   * Write response body
   *
   * @param  scriptlet.Response response
   * @param  peer.URL base
   * @param  string format
   */
  protected function writeBody($response, $base, $format) {
    $output= $response->getOutputStream();
    $this->buffered || $output->flush();
    $this->write($output);
  }

  /**
   * Returns whether a given value is equal to this Response instance
   *
   * @param  var cmp
   * @return bool
   */
  public function equals($cmp) {
    return (
      parent::equals($cmp) &&
      $this->mediaType === $cmp->mediaType &&
      $this->contentLength === $cmp->contentLength &&
      Objects::equal($this->inputStream, $cmp->inputStream)
    );
  }
}
