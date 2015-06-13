<?php namespace webservices\rest\srv;

use peer\http\HttpConstants;
use scriptlet\Preference;
use webservices\rest\RestFormat;
use webservices\rest\Payload;
use lang\XPClass;

/**
 * REST scriptlet
 *
 * @test  xp://net.xp_framework.unittest.webservices.rest.srv.RestScriptletTest
 */
class RestScriptlet extends \scriptlet\HttpScriptlet implements \util\log\Traceable {
  protected 
    $cat     = null,
    $router  = null,
    $context = null,
    $base    = '';

  /**
   * Constructor
   * 
   * @param  string package The package containing handler classes
   * @param  string base The base URL (will be stripped off from request url)
   * @param  string|webservices.rest.srv.RestContext context The context to use
   * @param  string|webservices.rest.srv.AbstractRestRouter router The router class to use
   */
  public function __construct($package, $base= '', $context= null, $router= null) {
    $this->base= rtrim($base, '/');
    $this->setContext($context);
    $this->setRouter($router);

    $this->router->configure($package, $this->base);
    $this->router->setInputFormats(['*json', '*xml', 'application/x-www-form-urlencoded']);
    $this->router->setOutputFormats(['application/json', 'text/json', 'text/xml', 'application/xml']);
  }

  /**
   * Set a log category for tracing
   *
   * @param  util.log.LogCategory cat
   */
  public function setTrace($cat) {
    $this->cat= $cat;
    $this->context->setTrace($this->cat);
  }

  /**
   * Sets a router
   *
   * @param  string|webservices.rest.srv.AbstractRestRouter router
   */
  public function setRouter($router) {
    if ($router instanceof AbstractRestRouter) {
      $this->router= $router;
    } else if ('' === (string)$router) {
      $this->router= new RestDefaultRouter();
    } else {
      $this->router= XPClass::forName($router)->newInstance();
    }
  }

  /**
   * Gets the router
   *
   * @return webservices.rest.srv.AbstractRestRouter
   */
  public function getRouter() {
    return $this->router;
  }

  /**
   * Sets a context
   *
   * @param  string|webservices.rest.srv.RestContext context
   */
  public function setContext($context) {
    if ($context instanceof RestContext) {
      $this->context= $context;
    } else if ('' === (string)$context) {
      $this->context= new RestContext();
    } else {
      $this->context= XPClass::forName($context)->newInstance();
    }
  }

  /**
   * Gets the context
   *
   * @return webservices.rest.srv.RestContext
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * Calculate method to invoke
   *
   * @param   scriptlet.HttpScriptletRequest request 
   * @return  string
   */
  public function handleMethod($request) {
    parent::handleMethod($request);
    return 'doProcess';
  }

  /**
   * Returns content-type of string if a payload is available, NULL otherwise.
   *
   * @see    http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.3
   * @see    http://www.w3.org/Protocols/rfc2616/rfc2616-sec7.html#sec7.2.1
   * @param  scriptlet.HttpScriptletRequest request The request
   * @return string
   */
  public function contentTypeOf($request) {
    if ($request->getHeader('Content-Length') || $request->getHeader('Transfer-Encoding')) {
      return $request->getHeader('Content-Type', 'application/octet-stream');
    }
    return null;
  }

  /**
   * Process request and handle errors
   * 
   * @param  scriptlet.HttpScriptletRequest request The request
   * @param  scriptlet.HttpScriptletResponse response The response
   */
  public function doProcess($request, $response) {
    $url= $request->getURL();
    $type= $this->contentTypeOf($request);
    $accept= new Preference($request->getHeader('Accept', '*/*'));
    $this->cat && $this->cat->info($request->getMethod(), $type ?: '(null)', $url->getURL(), $accept);

    // Iterate over all applicable routes
    $ctx= clone $this->context;
    foreach ($this->router->targetsFor(
      $request->getMethod(), 
      $url->getPath(), 
      $type,
      $accept
    ) as $target) {
      if ($ctx->process($target, $request, $response)) return;
    }

    // No route
    $response->setStatus(HttpConstants::STATUS_NOT_FOUND);
    $format= $accept->match($this->router->getOutputFormats());
    $response->setContentType($format);
    RestFormat::forMediaType($format)->write($response->getOutputStream(), new Payload(
      ['message' => 'Could not route request to '.$url->getURL()],
      ['name' => 'error']
    ));
  }
}
