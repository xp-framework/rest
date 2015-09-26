<?php namespace webservices\rest;

use text\StreamTokenizer;


/**
 * A deserializer for form data
 *
 * @see   xp://webservices.rest.RestDeserializer
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestFormDeserializerTest
 */
class RestFormDeserializer extends RestDeserializer {

  /**
   * Deserialize
   *
   * @param   io.streams.InputStream in
   * @return  var
   * @throws  lang.FormatException
   */
  public function deserialize($in) {
    $st= new StreamTokenizer($in, '&');
    $map= [];
    while ($st->hasMoreTokens()) {
      $key= $value= null;
      if (2 !== sscanf($t= $st->nextToken(), "%[^=]=%[^\r]", $key, $value)) {
        throw new \lang\FormatException('Malformed pair "'.$t.'"');
      }
      $key= urldecode($key);
      if (substr_count($key, '[') !== substr_count($key, ']')) {
        throw new \lang\FormatException('Unbalanced [] in query string');
      }
      if ($start= strpos($key, '[')) {    // Array notation
        $base= substr($key, 0, $start);
        isset($map[$base]) || $map[$base]= [];
        $ptr= &$map[$base];
        $offset= 0;
        do {
          $end= strpos($key, ']', $offset);
          if ($start === $end- 1) {
            $ptr= &$ptr[];
          } else {
            $end+= substr_count($key, '[', $start+ 1, $end- $start- 1);
            $ptr= &$ptr[substr($key, $start+ 1, $end- $start- 1)];
          }
          $offset= $end+ 1;
        } while ($start= strpos($key, '[', $offset));
        $ptr= urldecode($value);
      } else {
        $map[$key]= urldecode($value);
      }
    }
    return $map;
  }
}
