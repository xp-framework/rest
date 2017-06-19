<?php namespace webservices\rest\unittest;



/**
 * Issue
 *
 */
class IssueWithUnderscoreSetter {
  protected $issue_id= 0;
  protected $title= null;
  
  /**
   * Constructor
   *
   * @param   int issue_id
   * @param   string title
   */
  public function __construct($issue_id= 0, $title= null) {
    $this->issue_id= $issue_id;
    $this->title= $title;
  }

  /**
   * Set title
   *
   * @param   string title
   */
  public function setTitle($title) {
    $this->title= $title;
  }

  /**
   * Set issue_id
   *
   * @param   int issue_id
   */
  public function setIssue_id($issue_id) {
    $this->issue_id= $issue_id;
  }
  
  /**
   * Checks whether another object is equal to this issue
   *
   * @param   var cmp
   * @return  bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->issue_id === $this->issue_id && $cmp->title === $this->title;
  }
}
