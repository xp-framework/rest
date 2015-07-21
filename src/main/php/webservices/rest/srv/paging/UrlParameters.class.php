<?php namespace webservices\rest\srv\paging;

class UrlParameters extends \lang\Object implements Behavior {
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
   * Returns the current page
   *
   * @param  scriptlet.Request $request
   * @return int The page or NULL if the parameter was omitted
   */
  public function page($request) { return $request->getParam($this->page, null); }

  /**
   * Returns the current limit
   *
   * @param  scriptlet.Request $request
   * @return int The page or NULL if the parameter was omitted
   */
  public function limit($request) { return $request->getParam($this->limit, null); }

  /**
   * Paginate
   *
   * @param  scriptlet.Request $request
   * @param  webservices.rest.srv.Response $response
   * @param  bool $last
   * @return webservices.rest.srv.Response
   */
  public function paginate($request, $response, $last) {
    $page= $this->page($request);
    $header= new Links([
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