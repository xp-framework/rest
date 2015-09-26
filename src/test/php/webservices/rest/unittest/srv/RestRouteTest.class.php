<?php namespace webservices\rest\unittest\srv;

use unittest\TestCase;
use webservices\rest\srv\RestRoute;

/**
 * Test default router
 *
 * @see  xp://webservices.rest.srv.RestRoute
 */
class RestRouteTest extends TestCase {
  protected $handler= null;
  protected $target= null;

  /**
   * Setup
   * 
   */
  public function setUp() {
    $this->handler= $this->getClass();
    $this->target= $this->handler->getMethod('fixtureTarget');
  }

  #[@webservice]
  public function fixtureTarget() {
    // Intentionally empty
  }

  #[@test]
  public function verb() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals('GET', $r->getVerb());
  }

  #[@test]
  public function verb_is_uppercased() {
    $r= new RestRoute('Get', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals('GET', $r->getVerb());
  }

  #[@test]
  public function path() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals('/resource', $r->getPath());
  }

  #[@test]
  public function handler() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals($this->handler, $r->getHandler());
  }

  #[@test]
  public function target() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals($this->target, $r->getTarget());
  }

  #[@test]
  public function accepts() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, ['text/json'], null);
    $this->assertEquals(['text/json'], $r->getAccepts());
  }

  #[@test]
  public function accepts_default() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals(['text/json'], $r->getAccepts((array)'text/json'));
  }

  #[@test]
  public function produces() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, ['text/json']);
    $this->assertEquals(['text/json'], $r->getProduces());
  }

  #[@test]
  public function produces_default() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals(['text/json'], $r->getProduces((array)'text/json'));
  }

  #[@test]
  public function pattern() {
    $r= new RestRoute('GET', '/resource', $this->handler, $this->target, null, null);
    $this->assertEquals('#^/resource$#', $r->getPattern());
  }

  /**
   * Test getPattern()
   * 
   */
  #[@test]
  public function pattern_with_placeholder() {
    $r= new RestRoute('GET', '/resource/{id}', $this->handler, $this->target, null, null);
    $this->assertEquals('#^/resource/(?P<id>[^/]+)$#', $r->getPattern());
  }

  /**
   * Test getPattern()
   * 
   */
  #[@test]
  public function pattern_with_two_placeholders() {
    $r= new RestRoute('GET', '/resource/{id}/{sub}', $this->handler, $this->target, null, null);
    $this->assertEquals('#^/resource/(?P<id>[^/]+)/(?P<sub>[^/]+)$#', $r->getPattern());
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function string_representation() {
    $r= new RestRoute('GET', '/resource/{id}/{sub}', $this->handler, $this->target, null, null);
    $this->assertEquals(
      'webservices.rest.srv.RestRoute(GET /resource/{id}/{sub} -> var webservices.rest.unittest.srv.RestRouteTest::fixtureTarget())', 
      $r->toString()
    );
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function string_representation_with_produces() {
    $r= new RestRoute('GET', '/resource/{id}/{sub}', $this->handler, $this->target, null, ['text/json']);
    $this->assertEquals(
      'webservices.rest.srv.RestRoute(GET /resource/{id}/{sub} -> var webservices.rest.unittest.srv.RestRouteTest::fixtureTarget() @ text/json)', 
      $r->toString()
    );
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function string_representation_with_accepts_and_produces() {
    $r= new RestRoute('GET', '/resource/{id}/{sub}', $this->handler, $this->target, ['text/xml'], ['text/json']);
    $this->assertEquals(
      'webservices.rest.srv.RestRoute(GET /resource/{id}/{sub} @ text/xml -> var webservices.rest.unittest.srv.RestRouteTest::fixtureTarget() @ text/json)', 
      $r->toString()
    );
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function string_representation_with_param() {
    $r= new RestRoute('GET', '/resource/{id}', $this->handler, $this->target, null, null);
    $r->addParam('id', new \webservices\rest\srv\RestParamSource('id', \webservices\rest\srv\ParamReader::forName('path')));
    $this->assertEquals(
      'webservices.rest.srv.RestRoute(GET /resource/{id} -> var webservices.rest.unittest.srv.RestRouteTest::fixtureTarget(@$id: path(\'id\')))', 
      $r->toString()
    );
  }

  /**
   * Test toString()
   *
   */
  #[@test]
  public function string_representation_with_params() {
    $r= new RestRoute('GET', '/resource/{id}/{sub}', $this->handler, $this->target, null, null);
    $r->addParam('id', new \webservices\rest\srv\RestParamSource('id', \webservices\rest\srv\ParamReader::forName('path')));
    $r->addParam('sub', new \webservices\rest\srv\RestParamSource('sub', \webservices\rest\srv\ParamReader::forName('path')));
    $this->assertEquals(
      'webservices.rest.srv.RestRoute(GET /resource/{id}/{sub} -> var webservices.rest.unittest.srv.RestRouteTest::fixtureTarget(@$id: path(\'id\'), @$sub: path(\'sub\')))', 
      $r->toString()
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_matches_resource_and_subresource() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertEquals(
      [0 => '/binford/6100/chainsaw', 'id' => '6100', 1 => '6100', 'name' => 'chainsaw', 2 => 'chainsaw'],
      $r->appliesTo('/binford/6100/chainsaw')
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_matches_resource_and_subresource_with_star() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertEquals(
      [0 => '/binford/61/*', 'id' => '61', 1 => '61', 'name' => '*', 2 => '*'],
      $r->appliesTo('/binford/61/*')
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_matches_resource_and_subresource_with_dot() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertEquals(
      [0 => '/binford/61/.', 'id' => '61', 1 => '61', 'name' => '.', 2 => '.'],
      $r->appliesTo('/binford/61/.')
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_matches_resource_and_subresource_with_urlencoded() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertEquals(
      [0 => '/binford/61/%40', 'id' => '61', 1 => '61', 'name' => '%40', 2 => '%40'],
      $r->appliesTo('/binford/61/%40')
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_matches_resource_with_dash() {
    $r= new RestRoute('GET', '/binford/{id}-{name}', null, null, null, null);
    $this->assertEquals(
      [0 => '/binford/610-scissors', 'id' => '610', 1 => '610', 'name' => 'scissors', 2 => 'scissors'],
      $r->appliesTo('/binford/610-scissors')
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_matches_resource_with_prefix() {
    $r= new RestRoute('GET', '/binford/power{name}', null, null, null, null);
    $this->assertEquals(
      [0 => '/binford/powercar', 'name' => 'car', 1 => 'car'],
      $r->appliesTo('/binford/powercar')
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_matches_resource_with_postfix() {
    $r= new RestRoute('GET', '/binford/{name}power', null, null, null, null);
    $this->assertEquals(
      [0 => '/binford/morepower', 'name' => 'more', 1 => 'more'],
      $r->appliesTo('/binford/morepower')
    );
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_does_not_match_empty_path_segment() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertEquals(null, $r->appliesTo('/binford//chainsaw'));
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_does_not_match_base() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertNull($r->appliesTo('/binford'));
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_does_not_match_partial() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertNull($r->appliesTo('/binford/6100'));
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_does_not_match_partial_with_trailing_slash() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertNull($r->appliesTo('/binford/6100/'));
  }

  /**
   * Test appliesTo()
   *
   */
  #[@test]
  public function applies_to_does_not_match_partial_with_trailing_slashes() {
    $r= new RestRoute('GET', '/binford/{id}/{name}', null, null, null, null);
    $this->assertNull($r->appliesTo('/binford/6100//'));
  }
}
