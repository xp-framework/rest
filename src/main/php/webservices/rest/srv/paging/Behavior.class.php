<?php namespace webservices\rest\srv\paging;

interface Behavior {

  /**
   * Returns whether this behavior paginates a given request. Always returns true,
   * as omitting the page and limit parameters will paginate with the defaults!
   *
   * @param  scriptlet.Request $request
   */
  public function paginates($request);

  /**
   * Returns the starting offset set via the request
   *
   * @param  scriptlet.Request $request
   * @param  int $size
   * @return var The offset or NULL if the parameter was omitted
   */
  public function start($request, $size);

  /**
   * Returns the ending offset set via the request
   *
   * @param  scriptlet.Request $request
   * @param  int $size
   * @return var The offset or NULL if the parameter was omitted
   */
  public function end($request, $size);

  /**
   * Returns a limit set via the request
   *
   * @param  scriptlet.Request $request
   * @param  int $size
   * @return int The limit or NULL if the parameter was omitted
   */
  public function limit($request, $size);

  /**
   * Paginate
   *
   * @param  scriptlet.Request $request
   * @param  webservices.rest.srv.Response $response
   * @param  bool $last
   * @return webservices.rest.srv.Response
   */
  public function paginate($request, $response, $last);
}