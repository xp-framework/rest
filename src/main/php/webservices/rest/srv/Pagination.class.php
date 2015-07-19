<?php namespace webservices\rest\srv;

use lang\IllegalArgumentException;

class Pagination extends \lang\Object {
  private $request, $limit, $pageParam, $limitParam;

  /**
   * Unmarshalling from request
   *
   * @param  scriptlet.Request $request
   * @param  int $size Default elements per page
   * @param  string $pageParam "Page" parameter name
   * @param  string $limitParam "Per Page" parameter name
   * @return self
   */
  public static function valueOf($request, $size= 20, $pageParam= 'page', $limitParam= 'per_page') {
    $self= new self();
    $self->request= $request;
    $self->size= $size;
    $self->pageParam= $pageParam;
    $self->limitParam= $limitParam;
    return $self;
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
  public function page() { return $this->request->getParam($this->pageParam, 1); }

  /**
   * Returns the limit passed in the request's "Per Page" paramenter, or the default limit of omitted
   *
   * @return int
   */
  public function limit() { return $this->request->getParam($this->limitParam, $this->size); }

  /**
   * Returns the starting offset
   *
   * @return int
   */
  public function start() {
    return ($this->page() - 1) * $this->limit();
  }

  /**
   * Returns the starting offset
   *
   * @return int
   */
  public function end() {
    return $this->page() * $this->limit();
  }

  /**
   * Returns the URL for the previous page, or NULL if we're at page 1
   *
   * @return peer.URL
   */
  public function prev() {
    if ($this->page() > 1) {
      $prev= clone $this->request->getURL();
      return $prev->setParam($this->pageParam, $this->page() - 1);
    } else {
      return null;
    }
  }

  /**
   * Returns the URL for the next page
   *
   * @return peer.URL
   */
  public function next() {
    $next= clone $this->request->getURL();
    return $next->setParam($this->pageParam, $this->page() + 1);
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