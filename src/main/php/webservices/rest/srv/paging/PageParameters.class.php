<?php namespace webservices\rest\srv\paging;

/**
 * The URL parameters paging behavior uses two parameters from the request's
 * query string to determine page and limit. When the parameter referrning to
 * the current page is omitted from the URL this behavior regards this as the
 * first page. The limit can be used to overwrite the default paging limit set
 * via the `Paging` class.
 *
 * @test  xp://webservices.rest.unittest.srv.paging.UrlParametersTest
 */
class PageParameters extends \lang\Object implements Behavior {
  private $page, $limit;

  /**
   * Creates a new parameters instance
   *
   * @param  string $page Name of parameter referring to the current page
   * @param  string $page Name of parameter referring to the optional limit
   */
  public function __construct($page, $limit) {
    $this->page= $page;
    $this->limit= $limit;
  }

  /**
   * Returns URL with a given page
   *
   * @param  scriptlet.Request $request
   * @param  int $page
   * @return peer.URL
   */
  private function urlWithPage($request, $page) {
    $url= clone $request->getURL();
    return $url->setParam($this->page, $page);
  }

  /**
   * Returns whether this behavior paginates a given request. Always returns true,
   * as omitting the page and limit parameters will paginate with the defaults!
   *
   * @param  scriptlet.Request $request
   */
  public function paginates($request) { return true; }

  /**
   * Returns the starting offset set via the request, or NULL if none was given.
   *
   * @param  scriptlet.Request $request
   * @param  int $size
   * @return var
   */
  public function start($request, $size) {
    $page= $request->getParam($this->page, null);
    return null === $page ? null : ($page - 1) * $this->limit($request, $size);
  }

  /**
   * Returns the ending offset set via the request
   *
   * @param  scriptlet.Request $request
   * @param  int $size
   * @return var The offset or NULL if the parameter was omitted
   */
  public function end($request, $size) {
    return $this->start($request, $size) + $this->limit($request, $size);
  }

  /**
   * Returns the current limit
   *
   * @param  scriptlet.Request $request
   * @param  int $size
   * @return int
   */
  public function limit($request, $size) {
    return (int)$request->getParam($this->limit, $size);
  }

  /**
   * Paginate
   *
   * @param  scriptlet.Request $request
   * @param  webservices.rest.srv.Response $response
   * @param  bool $last
   * @return webservices.rest.srv.Response
   */
  public function paginate($request, $response, $last) {
    $page= $request->getParam($this->page, 1);
    $header= new LinkHeader([
      'prev' => $page > 1 ? $this->urlWithPage($request, $page - 1) : null,
      'next' => $last ? null : $this->urlWithPage($request, $page + 1)
    ]);

    if ($header->present()) {
      return $response->withHeader('Link', $header);
    } else {
      return $response;
    }
  }
}