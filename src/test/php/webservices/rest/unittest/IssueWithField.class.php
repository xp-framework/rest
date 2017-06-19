<?php namespace webservices\rest\unittest;



/**
 * Issue
 *
 */
class IssueWithField {
  #[@type('int')]
  public $issueId= 0;
  #[@type('string')]
  public $title= null;
  
  /**
   * Constructor
   *
   * @param   int issueId
   * @param   string title
   */
  public function __construct($issueId= 0, $title= null) {
    $this->issueId= $issueId;
    $this->title= $title;
  }
  
  /**
   * Checks whether another object is equal to this issue
   *
   * @param   var cmp
   * @return  bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->issueId === $this->issueId && $cmp->title === $this->title;
  }
}
