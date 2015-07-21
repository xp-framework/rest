<?php namespace webservices\rest\srv\paging;

use peer\URL;

class Links extends \lang\Object {
  private $links= [];

  /**
   * Creates a new "Link" header
   *
   * @param  [:var] $links A map of rel -> url
   */
  public function __construct($links) {
    foreach ($links as $rel => $link) {
      if ($link instanceof URL) {
        $this->links[$rel]= $link;
      } else if ($link) {
        $this->links[$rel]= new URL($link);
      }
    }
  }

  /** @return bool */
  public function present() { return !empty($this->links); }

  /** @return string */
  public function __toString() {
    $return= '';
    foreach ($this->links as $rel => $link) {
      $return.= ', <'.$link->getURL().'>; rel="'.$rel.'"';
    }
    return (string)substr($return, 2);
  }
}