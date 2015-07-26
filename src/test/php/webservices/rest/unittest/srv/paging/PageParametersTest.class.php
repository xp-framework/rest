<?php namespace webservices\rest\unittest\srv\paging;

use peer\URL;
use webservices\rest\srv\Response;
use webservices\rest\srv\paging\PageParameters;
use webservices\rest\srv\paging\LinkHeader;
use scriptlet\HttpScriptletRequest;

class PageParametersTest extends \unittest\TestCase {
  const BASE_URL = 'http://example.com/';
  const SIZE = 20;

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
    $this->fixture= new PageParameters('page', 'per_page');
  }

  #[@test]
  public function paginates() {
    $this->assertTrue($this->fixture->paginates($this->newRequest()));
  }

  #[@test]
  public function start_for_empty_request() {
    $this->assertNull($this->fixture->start($this->newRequest(), self::SIZE));
  }

  #[@test]
  public function start_in_request() {
    $this->assertEquals(0, $this->fixture->start($this->newRequest('?page=1'), self::SIZE));
  }

  #[@test]
  public function end_for_empty_request() {
    $this->assertEquals(self::SIZE, $this->fixture->end($this->newRequest(), self::SIZE));
  }

  #[@test]
  public function end_via_limit_in_request() {
    $this->assertEquals(10, $this->fixture->end($this->newRequest('?page=2&per_page=5'), self::SIZE));
  }

  #[@test]
  public function limit_for_empty_request() {
    $this->assertEquals(self::SIZE, $this->fixture->limit($this->newRequest(), self::SIZE));
  }

  #[@test]
  public function limit_in_request() {
    $this->assertEquals(5, $this->fixture->limit($this->newRequest('?per_page=5'), self::SIZE));
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
      ['Link' => new LinkHeader(['next' => self::BASE_URL.'?page=2'])],
      $this->fixture->paginate($this->newRequest('?page=1'), Response::ok(), false)->headers
    );
  }

  #[@test]
  public function next_and_prev_header_on_second_page() {
    $this->assertEquals(
      ['Link' => new LinkHeader(['prev' => self::BASE_URL.'?page=1', 'next' => self::BASE_URL.'?page=3'])],
      $this->fixture->paginate($this->newRequest('?page=2'), Response::ok(), false)->headers
    );
  }

  #[@test]
  public function prev_header_on_last_page() {
    $this->assertEquals(
      ['Link' => new LinkHeader(['prev' => self::BASE_URL.'?page=1'])],
      $this->fixture->paginate($this->newRequest('?page=2'), Response::ok(), true)->headers
    );
  }
}