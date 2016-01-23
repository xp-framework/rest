<?php namespace webservices\rest\srv\paging;

use scriptlet\Request;

/**
 * A pagination instance holds the paging behavior, the request and the
 * page size. It is created by passing a request instance to the Paging
 * class' `on()` method.
 *
 * @test  xp://webservices.rest.unittest.srv.paging.PaginationTest
 */
class Pagination extends \lang\Object {
  private $request, $size, $behavior;

  /**
   * Creates a new pagination instance
   *
   * @param  scriptlet.Request $request
   * @param  webservices.rest.srv.paging.Behavior $behavior
   * @param  int $size
   */
  public function __construct(Request $request, $behavior, $size) {
    $this->request= $request;
    $this->size= $size;
    $this->behavior= $behavior;
  }

  /**
   * Returns the starting offset, or the supplied default of omitted
   *
   * @param  var $default
   * @return var
   */
  public function start($default= null) {
    return $this->behavior->start($this->request, $this->size) ?: $default;
  }

  /**
   * Returns the ending offset, or the supplied default of omitted
   *
   * @param  var $default
   * @return var
   */
  public function end($default= null) {
    return $this->behavior->end($this->request, $this->size) ?: $default;
  }

  /**
   * Returns the limit passed in the request's limit paramenter, or the default limit of omitted
   *
   * @return int
   */
  public function limit() {
    return $this->behavior->limit($this->request, $this->size);
  }

  /**
   * Paginate
   *
   * @param  webservices.rest.srv.Response $response
   * @param  var[] $elements
   * @return webservices.rest.srv.Response
   */
  public function paginate($response, array $elements) {
    $limit= $this->limit();
    $last= sizeof($elements) <= $limit;
    while (sizeof($elements) > $limit) {
      array_pop($elements);
    }
    return $this->behavior->paginate($this->request, $response, $last)->withPayload($elements);
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      '%s@([%s..%s], limit= %d)',
      nameof($this),
      $this->start(''),
      $this->end(''),
      $this->limit()
    );
  }
}