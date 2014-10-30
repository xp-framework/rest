<?php namespace webservices\rest;

class Iteration extends \lang\Object implements \Iterator {
  protected $it, $mapping;

  /**
   * Creates a new iteration
   *
   * @param  var $traversable Either a traversable or an array
   * @param  function(var): var $mapping
   */
  public function __construct($traversable, $mapping) {
    if ($traversable instanceof \Iterator) {
      $this->it= $traversable;
    } else if ($traversable instanceof \IteratorAggregate) {
      $this->it= $traversable->getIterator();
    } else {
      $this->it= new \ArrayIterator($traversable);
    }
    $this->mapping= $mapping;
  }

  /** @return void */
  public function rewind() {
    $this->it->rewind();
  }

  /** @return var */
  public function current() {
    $f= $this->mapping;
    return $f($this->it->current());
  }

  /** @return var */
  public function key() {
    return $this->it->key();
  }

  /** @return void */
  public function next() {
    $this->it->next();
  }

  /** @return bool */
  public function valid() {
    return $this->it->valid();
  }
}