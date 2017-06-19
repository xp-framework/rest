<?php namespace webservices\rest\srv\paging;

use util\NoSuchElementException;

class Paging {
  private $size, $behaviors;

  /**
   * Creates a new paging instance
   *
   * @param  int $size
   * @param  webservices.rest.srv.paging.Behavior[] $behaviors
   */
  public function __construct($size, array $behaviors) {
    $this->size= $size;
    $this->behaviors= $behaviors;
  }

  /**
   * Paging
   *
   * @param  scriptlet.Request $request
   * @return webservices.rest.srv.paging.Pagination
   * @throws util.NoSuchElementException
   */
  public function on($request) {
    foreach ($this->behaviors as $behavior) {
      if ($behavior->paginates($request)) return new Pagination($request, $behavior, $this->size);
    }
    throw new NoSuchElementException('No pagination behavior applies to the request');
  }
}