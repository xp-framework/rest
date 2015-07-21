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
   * Returns the current page
   *
   * @param  scriptlet.Request $request
   * @return int The page or NULL if the parameter was omitted
   */
  public function page($request);

  /**
   * Returns the current limit
   *
   * @param  scriptlet.Request $request
   * @return int The page or NULL if the parameter was omitted
   */
  public function limit($request);

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