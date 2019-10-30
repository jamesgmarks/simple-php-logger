<?php

include 'logger.php';
include 'postgres_logger.php';

use \Postgres_Logger\Postgres_Logger;

$logger = new Postgres_Logger();
set_error_handler([$logger, 'error_handler'], E_ALL);
set_exception_handler([$logger, 'exception_handler']);

/**
 * @return string|mixed
 */
function array_get($array, $key, $default = null) {
  if(!$array) return $default ?: null;
  
  $return = null;
  if(array_key_exists($key, $array)) $return = $array[$key];
  
  return $return ?: null;
}

// Trigger an exception in a try block
class ZeroDivider {
  public function divideByZero() {
    try {
      $a = 3/0;
      echo $a;
    }
    catch(Exception $e) {
      echo 'Message: ' .$e->getMessage();
    }
  }
}

$z = new ZeroDivider();

$z->divideByZero();

// $host='postgres';
// $db = 'postgres';
// $username = 'postgres';
// $password = 'docker';

// $dsn = "pgsql:host=$host;port=5432;dbname=$db;user=$username;password=$password";

// try{
//   // create a PostgreSQL database connection
//   $conn = new PDO($dsn);

//   // display a message if connected to the PostgreSQL successfully
//   if($conn){
//     echo "Connected to the <strong>$db</strong> database successfully!";
//   }
// }catch (PDOException $e){
//   // report error message
//   echo "PDO Exception";
//   echo $e->getMessage();
// }

$logger->log([ "Test" ]);

class ErrorGenerator2 {
  public static function generate_error() {
    try {
      $a = 3/0;
      throw new Exception("Anything can happen");
    } catch (Exception $ex) {
      echo "threw an exception";
    }
  }
}

$gen = new ErrorGenerator2();
$gen->generate_error();

throw new Exception("Just another test");