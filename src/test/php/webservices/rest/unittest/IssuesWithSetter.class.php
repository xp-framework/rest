<?php namespace webservices\rest\unittest;

/**
 * Issues
 *
 */
class IssuesWithSetter extends \lang\Object {
  protected $issues= null;

  /**
   * Constructor
   *
   * @param   webservices.rest.unittest.IssueWithField[] issues
   */
  public function __construct($issues= null) {
    $this->issues= $issues;
  }

  /**
   * Sets issues
   *
   * @param   webservices.rest.unittest.IssueWithField[] issues
   */
  public function setIssues($issues) {
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
