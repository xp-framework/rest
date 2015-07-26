<?php namespace webservices\rest\unittest\srv\paging;

use peer\URL;
use webservices\rest\srv\Response;
use webservices\rest\srv\paging\Pagination;
use webservices\rest\srv\paging\PageParameters;
use scriptlet\HttpScriptletRequest;

class PaginationTest extends \unittest\TestCase {
  const SIZE = 50;

  /**
   * Creates a new request instance
   *
   * @param  string $queryString
   * @return webservices.rest.srv.paging.Pagination
   */
  protected function newFixture($queryString= '') {
    parse_str(ltrim($queryString, '?'), $params);

    $r= new HttpScriptletRequest();
    $r->setParams($params);
    $r->setURI(new URL('http://example.com/'.$queryString));

    return new Pagination($r, new PageParameters('page', 'per_page'), self::SIZE);
  }

  #[@test]
  public function can_create() {
    $this->newFixture();
  }

  #[@test]
  public function size() {
    $this->assertEquals(self::SIZE, $this->newFixture()->size());
  }

  #[@test]
  public function page_defaults_to_1() {
    $this->assertEquals(1, $this->newFixture()->page());
  }

  #[@test]
  public function page_explicitely_given() {
    $this->assertEquals(2, $this->newFixture('?page=2')->page());
  }

  #[@test]
  public function limit_defaults_to_size() {
    $this->assertEquals(self::SIZE, $this->newFixture()->limit());
  }

  #[@test]
  public function limit_explicitely_given() {
    $this->assertEquals(10, $this->newFixture('?per_page=10')->limit());
  }

  #[@test, @values([
  #  ['', 0],
  #  ['?page=1', 0],
  #  ['?page=2', self::SIZE],
  #  ['?page=1&per_page=10', 0],
  #  ['?page=2&per_page=10', 10]
  #])]
  public function start($queryString, $offset) {
    $this->assertEquals($offset, $this->newFixture($queryString)->start());
  }

  #[@test, @values([
  #  ['', self::SIZE],
  #  ['?per_page=10', 10],
  #  ['?page=1&per_page=10', 10],
  #  ['?page=2&per_page=10', 20]
  #])]
  public function end($queryString, $offset) {
    $this->assertEquals($offset, $this->newFixture($queryString)->end());
  }

  #[@test]
  public function paginate_on_empty() {
    $this->assertEquals([], $this->newFixture()->paginate(Response::ok(), [])->payload->value);
  }

  #[@test, @values([1, 2, self::SIZE])]
  public function paginate($size) {
    $elements= array_fill(0, $size, 'element');
    $this->assertEquals(
      $elements,
      $this->newFixture()->paginate(Response::ok(), $elements)->payload->value
    );
  }

  #[@test, @values([1, 2, self::SIZE])]
  public function paginate_removes_excess_elements_from_payload($by) {
    $this->assertEquals(
      array_fill(0, self::SIZE, 'element'),
      $this->newFixture()->paginate(Response::ok(), array_fill(0, self::SIZE + $by, 'element'))->payload->value
    );
  }

  #[@test]
  public function string_representation() {
    $this->assertEquals(
      'webservices.rest.srv.paging.Pagination@(page= 1, limit= 50)',
      $this->newFixture()->toString()
    );
  }
}