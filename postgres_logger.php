<?php
namespace Postgres_Logger;

use \Logger\Logger;
use \Logger\Error;

/**
 * Extends Logger such that messages are recorded to a postgres DB.
 */
class Postgres_Logger extends Logger {
  function log($metadata) {
    echo("Just logging first element to stdout: " . $metadata[0]);
  }
  function log_error(Error $error) {

    $traces = $error->getTraces();

    echo("<h1>ERROR!!!!</h1>");
    echo("<h2>" . $error->errstr . "</h2>");
    echo "<ul>";
    $i = 0;
    foreach($traces as $t) {
      echo ("<li>{$i}: {$t->file} - {$t->class}::{$t->function} ({$t->line})</li>");
      $i++;
    }
    echo "</ul>";


  }
}
