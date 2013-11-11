<?php namespace webservices\rest\srv;



/**
 * Exception mapping
 *
 */
interface ExceptionMapper {

  /**
   * Maps an exception
   *
   * @param  lang.Throwable t
   * @param  webservices.rest.srv.RestContext ctx
   * @return webservices.rest.srv.Response
   */
  public function asResponse($t, RestContext $ctx);
}
