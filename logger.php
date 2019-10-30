<?php
namespace Logger;

use \ErrorException;

class LoggerException extends ErrorException { }

/**
 * @property-read string $source
 * @property-read int $errno
 * @property-read string $errstr
 * @property-read string $errfile
 * @property-read int $errline
 * @property-read TraceCollection $traces
 * @property-read array $meta_data
 */
class Error {
  private $source;
  private $errno;
  private $errstr;
  private $errfile;
  private $errline;
  private $traces;
  private $meta_data = [];

  public function __construct(string $source, int $errno, string $errstr, string $errfile, int $errline, TraceCollection $traces, array $meta_data = null) {
    $this->source = $source;
    $this->errno = $errno;
    $this->errstr = $errstr;
    $this->errfile = $errfile;
    $this->errline = $errline;
    $this->traces = $traces;
    $this->meta_data = $meta_data;
  }

  public function getTraces() {
    return $this->traces->getTraces();
  }

  // TODO: ensure cloning or passing by value.
  public function __get($name) {
    if(property_exists(get_class($this), $name)) {
      return $this->{$name};
    }
  }
}

/**
 * @property-read string $class
 * @property-read string $file
 * @property-read string $function
 * @property-read int $line
 */
class Trace {
  private $class;
  private $file;
  private $function;
  private $line;
  
  public function __construct(string $class, string $file, string $function, int $line) {
    $this->class = $class;
    $this->file = $file;
    $this->function = $function;
    $this->line = $line;
  }

  public function __get($name) {
    if(property_exists(get_class($this), $name)) {
      return $this->{$name};
    }
  }
}

/**
 * @property-read Array<Trace> $traces
 */
class TraceCollection {
  private $traces = [];

  public function addItem(Trace $trace) : int {
    return array_push($this->traces, $trace);
  }

  public function deleteItemAt(int $index) : bool {
    if(count($this->traces) <= $index || $index < 0) return false;
    $this->traces = array_merge(array_slice($this->traces, 0, $index), array_slice($this->traces, $index));
    return true;
  }
  public function deleteItem(Trace $trace) : bool {
    $index = $this->indexOf($trace);
    if($index === -1) return false;
    return deleteItem($index);
  }

  public function indexOf(Trace $trace) {
    $index = array_search($trace, $this->traces);
  }

  public function getItemAt($index) : Trace {
    if(count($this->traces) <= $index || $index < 0) throw new \OutOfRangeException('Invalid Index');
    return $this->traces[$index];
  }

  public function getTraces() : array {
    $out = [];
    foreach($this->traces as $trace) {
      $out[] = $trace;
    }
    return $out;
  }

  public function __get($index) : Trace {
    if(!is_numeric($index)) throw new \InvalidArgumentException('Non-numeric Index');
    return getItemAt($index);
  }
}

/**
 * Abstraction to define methods expected in a logger sub-class.
 * `log_error()` ensures a mechanism to receive a structured error log request.
 * `log()` provides a logging endpoint where the data can be more arbitrary.
 */
abstract class Logger {
  public abstract function log(array $data);
  public abstract function log_error(Error $error);
  public function error_handler($errno, $errstr, $errfile, $errline) {
    $collection = $this->generate_trace_collection(debug_backtrace());
    $error = new Error("Exception Handler", $errno, $errstr, $errfile, $errline, $collection);

    $this->log_error($error);
    
    throw new LoggerException($errstr, $errno, 0, $errfile, $errline);     
  }
  public function exception_handler(\Throwable $throwable) {

    if(is_a($throwable, '\Logger\LoggerException')) {
      // It's already been recorded.
      return;
    }

    $collection = $this->generate_trace_collection($throwable->getTrace());
    $message = $throwable->getMessage();
    $code = $throwable->getCode();
    $file = $throwable->getFile();
    $line = $throwable->getLine();
    $error = new Error("Uncaught Exception", $code, $message, $file, $line, $collection);
    $this->log_error($error);
  }
  private function generate_trace_collection(array $traces) {
    $collection = new TraceCollection();

    // Remove logging calls from the history.
    if(count($traces) > 0)
      do {
        $class = array_get($traces[0], 'class');
        array_shift($traces);
      } while (stristr($class, get_class($this)));

    foreach($traces as $t) {
      /** @var string $class */
      $class = array_get($t, 'class', '<unknown class>');
      /** @var string $file */
      $file = array_get($t, 'file', '<unknown file>');
      /** @var string $function */
      $function = array_get($t, 'function', '<unknown function>');
      /** @var string $line */
      $line = array_get($t, 'line', '<unknown line>');
      $collection->addItem(new Trace($class, $file, $function, (int) $line));
    }
    return $collection;
  }
}