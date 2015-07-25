<?php namespace webservices\rest\unittest\srv\paging;

use peer\URL;
use webservices\rest\srv\Response;
use webservices\rest\srv\paging\UrlParameters;
use webservices\rest\srv\paging\Links;
use scriptlet\HttpScriptletRequest;

class UrlParametersTest extends \unittest\TestCase {
  const BASE_URL = 'http://example.com/';

  private $fixture;

  /**
   * Creates a new request instance
   *
   * @param  string $queryString
   * @return scriptlet.Request
   */
  protected function newRequest($queryString= '') {
    parse_str(ltrim($queryString, '?'), $params);

    $r= new HttpScriptletRequest();
    $r->setParams($params);
    $r->setURI(new URL(self::BASE_URL.$queryString));

    return $r;
  }

  /**
   * Creates fixture
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= new UrlParameters('page', 'per_page');
  }

  #[@test]
  public function paginates() {
    $this->assertTrue($this->fixture->paginates($this->newRequest()));
  }

  #[@test]
  public function page_for_empty_request() {
    $this->assertNull($this->fixture->page($this->newRequest()));
  }

  #[@test]
  public function page_in_request() {
    $this->assertEquals('1', $this->fixture->page($this->newRequest('?page=1')));
  }

  #[@test]
  public function limit_for_empty_request() {
    $this->assertNull($this->fixture->limit($this->newRequest()));
  }

  #[@test]
  public function limit_in_request() {
    $this->assertEquals('5', $this->fixture->limit($this->newRequest('?per_page=5')));
  }

  #[@test]
  public function no_headers_when_first_page_is_also_last_page() {
    $this->assertEquals(
      [],
      $this->fixture->paginate($this->newRequest('?page=1'), Response::ok(), true)->headers
    );
  }

  #[@test]
  public function next_header_on_first_page() {
    $this->assertEquals(
      ['Link' => new Links(['next' => self::BASE_URL.'?page=2'])],
      $this->fixture->paginate($this->newRequest('?page=1'), Response::ok(), false)->headers
    );
  }

  #[@test]
  public function next_and_prev_header_on_second_page() {
    $this->assertEquals(
      ['Link' => new Links(['prev' => self::BASE_URL.'?page=1', 'next' => self::BASE_URL.'?page=3'])],
      $this->fixture->paginate($this->newRequest('?page=2'), Response::ok(), false)->headers
    );
  }

  #[@test]
  public function prev_header_on_last_page() {
    $this->assertEquals(
      ['Link' => new Links(['prev' => self::BASE_URL.'?page=1'])],
      $this->fixture->paginate($this->newRequest('?page=2'), Response::ok(), true)->headers
    );
  }
}