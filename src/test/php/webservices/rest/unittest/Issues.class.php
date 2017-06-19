<?php namespace webservices\rest\unittest;

class Issues {
  private $backing= null;

  /**
   * Constructor
   *
   * @param  webservices.rest.unittest.IssueWithField...
   */
  public function __construct() {
    $this->backing= func_get_args();
  }

  /**
   * Constructor
   *
   * @param  webservices.rest.unittest.IssueWithField[] $arg
   * @return self
   */  
  public static function valueOf($arg) {
    $self= new self();
    $self->backing= $arg;
    return $self;
  }

  /** @return int */
  public function size() { return sizeof($this->backing); }

  /** @return webservices.rest.unittest.IssueWithField[] */
  public function all() { return $this->backing; }

  /**
   * Check whether another object is equal to this
   * 
   * @param   var cmp
   * @return  bool
   */
  public function equals($cmp) {
    if (!($cmp instanceof self)) return false;
    if (sizeof($this->backing) !== sizeof($cmp->backing)) return false;
    foreach ($this->backing as $i => $issue) {
      if (!$issue->equals($cmp->backing[$i])) return false;
    }
    return true;
  }

  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'@'.\xp::stringOf($this->backing);
  }
}
