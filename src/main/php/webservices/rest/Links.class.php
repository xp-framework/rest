<?php namespace webservices\rest;

use text\StringTokenizer;
use lang\FormatException;

/**
 * Link header
 *
 * @test xp://webservices.rest.unittest.LinksTest
 * @see  https://www.w3.org/wiki/LinkHeader
 * @see  https://tools.ietf.org/html/rfc5988 Web Linking
 */
class Links {
  private $links= [];

  /**
   * Parser helper function
   *
   * @param  text.Tokenizer $st
   * @param  string $tokens
   * @return string
   * @throws lang.FormatException
   */
  private function expect($st, $tokens) {
    $parsed= $st->nextToken($tokens);
    if (false === strpos($tokens, $parsed)) {
      throw new FormatException('Expected ['.$tokens.'], have '.\xp::stringOf($parsed));
    }
    return $parsed;
  }

  /**
   * Parses a Link header into a links structure
   *
   * @param  string $header
   * @throws lang.FormatException If the header is malformed
   */
  public function __construct($header) {
    $st= new StringTokenizer($header, '<>', true);
    do {
      $this->expect($st, '<');
      $uri= $st->nextToken('>');
      $this->expect($st, '>');

      $params= [];
      do {
        if (',' === $this->expect($st, ';,')) break;

        $param= ltrim($st->nextToken('='));
        $this->expect($st, '=');
        if ('"' === ($value= $st->nextToken('";,'))) {
          $value= $st->nextToken('"');
          $this->expect($st, '"');
        }
        $params[$param]= $value;
      } while ($st->hasMoreTokens());

      $this->links[]= new Link($uri, $params);
    } while ($st->nextToken('<'));
  }

  /**
   * Returns a map of link URIs to parameters, optionally restricted to a given search
   *
   * @param  [:string] $search If omitted, all links are returned
   * @return iterable
   */
  public function all($search= null) {
    if (null === $search) {
      foreach ($this->links as $link) {
        yield $link;
      }
    } else {
      foreach ($this->links as $link) {
        foreach ($search as $param => $compare) {
          if (!$link->hasParam($param) || (null !== $compare && $compare !== $link->param($param))) continue 2;
        }
        yield $link;
      }
    }
  }

  /**
   * Searches for the first link URI by a given search
   *
   * @param  [:string] $search
   * @param  string $default
   * @return string
   */
  public function uri($search, $default= null) {
    foreach ($this->all($search) as $link) {
      return $link->uri();
    }
    return $default;
  }

  /**
   * Creates a lookup map to by a given link parameter
   *
   * @param  string $param
   * @return [:webservices.rest.Link]
   */
  public function map($param) {
    $map= [];
    foreach ($this->links as $link) {
      if ($link->hasParam($param)) $map[$link->param($param)]= $link;
    }
    return $map;
  }
}