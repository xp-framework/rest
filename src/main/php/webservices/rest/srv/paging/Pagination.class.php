<?php namespace webservices\rest\srv\paging;

use scriptlet\Request;

class Pagination extends \lang\Object {
  private $request, $size, $behavior;

  public function __construct(Request $request, $behavior, $size) {
    $this->request= $request;
    $this->size= $size;
    $this->behavior= $behavior;
  }

  /**
   * Returns the page size
   *
   * @return int
   */
  public function size() { return $this->size; }

  /**
   * Returns the page passed in the request's "Page" parameter, or 1 if omitted
   *
   * @return int
   */
  public function page() { return $this->behavior->page($this->request) ?: 1; }

  /**
   * Returns the limit passed in the request's "Per Page" paramenter, or the default limit of omitted
   *
   * @return int
   */
  public function limit() { return $this->behavior->limit($this->request) ?: $this->size; }

  /**
   * Returns the starting offset
   *
   * @return int
   */
  public function start() {
    return ($this->page() - 1) * $this->limit();
  }

  /**
   * Returns the ending offset
   *
   * @return int
   */
  public function end() {
    return $this->page() * $this->limit();
  }

  /**
   * Paginate
   *
   * @param  webservices.rest.srv.Response $response
   * @param  var[] $elements
   * @return webservices.rest.srv.Response
   */
  public function paginate($response, array $elements) {
    $last= sizeof($elements) <= $this->limit();
    $last || array_pop($elements);
    return $this->behavior->paginate($this->request, $response, $last)->withPayload($elements);
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return $this->getClassName().'@(page= '.$this->page().', limit= '.$this->limit().')';
  }
}