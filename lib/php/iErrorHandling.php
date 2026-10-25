<?php

define('DEBUG',true);   // only for development
define('SEP', '&#x2714;' . '||' . '&#x2717;');

 
 error_reporting(E_ALL | E_STRICT); // everything

function errortype($v) {
  $errortype = array(
    E_ERROR           => 'error',
    E_WARNING         => 'warning',
    E_PARSE           => 'parsing error',
    E_NOTICE          => 'notice',
    E_CORE_ERROR      => 'core error',
    E_CORE_WARNING    => 'core warning',
    E_COMPILE_ERROR   => 'compile error',
    E_COMPILE_WARNING => 'compile warning',
    E_USER_ERROR      => 'user error',
    E_USER_WARNING    => 'user warning',
    E_USER_NOTICE     => 'user notice'
  );
  if(defined('E_STRICT'))  $errortype[E_STRICT] = 'runtime notice';
  return $errortype[$v];
}

function shutdown_function(){
  $err = error_get_last();
  if ($err) {  // if $err it should be an array
    // $err should be null because all errors have been handled
    echo 'An uncaught error in shutdown_function';
    var_dump($err);
  }
}
register_shutdown_function('shutdown_function');

 
class ErrorOrWarningException extends Exception {
  // I copied this class - I don't understand it
  protected $_Context = null;
  public function getContext() {
    return $this->_Context;
  }
  public function setContext( $value ) {
    $this->_Context = $value;
  }
  public function __construct($code, $message, $file, $line, $context) {
    parent::__construct($message, $code);
    $this->file = $file;
    $this->line = $line;
    $this->setContext($context);
  }
}

function error_to_exception($code, $message, $file, $line, $context=null) {
  throw new ErrorOrWarningException($code, $message, $file, $line, $context);
}
set_error_handler('error_to_exception');


function exception_handler( $ex ) {
  if (!isset($errLog)) $errLog = unserialize($_SESSION['errLog']);
  // send error to the error log
  ob_start();
  var_dump($ex);
  $s = ob_get_contents();
  ob_end_clean();
  $errLog->write('######## ' . date("H:i") . ' ########');
  $errLog->write($s);
  // print_r($ex);
  // var_dump($ex);
  // if we are in debug mode we are allowed to dump exceptions to the browser.
  if ( defined( 'DEBUG' ) && DEBUG == true ) {
    echo "******** from PHP exception handler ***********\n";
    echo $ex->getMessage() . "\n";
    echo 'file = ' . $ex->getFile() . "\n";
    echo 'line = ' . $ex->getLine() . "\n";
  } else {  // if we are in production we give our visitor a nice message without all the details.
    //echo file_get_contents( 'static/errors/fatalexception.html' );
  }
  exit;
}
set_exception_handler('exception_handler');


class Log {
  private $filename;
  function __construct($path,$name,$saveIn){
    $this->date = date("ymd");
    $this->filename = $path . $name . '_' . $this->date . '.log';
    if (!file_exists($this->filename)) {
      // if not exists then write the following
      $this->write('******************',false);
      $this->write(date("d/m/Y, H:i"),false);
      $this->write('******************',false);
    }
    $_SESSION[$saveIn] = serialize($this);
  }
  function write($note,$prefix = true){
    if (is_array($note)) $note = implode(',',$note);
    $fh = fopen($this->filename, 'a');
    if ($prefix){
      fwrite($fh, date("H:i") . " : {$note}\n");
    } else {
      fwrite($fh, "{$note}\n");
    }
    fclose($fh);
  }
}

function errMessage($e){
  $s = $e->getFile();
  $s = substr($s,strrpos($s,'\\') + 1);
  // the following is heredocs
  return <<< quote
  {$e->getMessage()} in {$s} on line {$e->getLine()}
  quote;
}



?>