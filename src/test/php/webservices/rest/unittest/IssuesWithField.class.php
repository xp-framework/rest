<?php namespace webservices\rest\unittest;



/**
 * Issues
 *
 */
class IssuesWithField extends \lang\Object {
  #[@type('webservices.rest.unittest.IssueWithField[]')]
  public $issues= null;

  /**
   * Constructor
   *
   * @param   net.xp_framework.unittest.webservices.rest.IssueWithField[] issues
   */
  public function __construct($issues= null) {
    $this->issues= $issues;
  }

  /**
   * Check whether another object is equal to this
   * 
   * @param   var cmp
   * @return  bool
   */
  public function equals($cmp) {
    if (!($cmp instanceof self)) return false;
    if (sizeof($this->issues) !== sizeof($cmp->issues)) return false;
    foreach ($this->issues as $i => $issue) {
      if (!$issue->equals($cmp->issues[$i])) return false;
    }
    return true;
  }

  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'@'.\xp::stringOf($this->issues);
  }
}
