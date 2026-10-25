<?php

session_start();   // nothing to come before this
header("content-type:text/html");

// This php file is the first responder (fr) for calls from js.
// Data is sent either:
  // a) without using FormData in which case we must use $data = json_decode(file_get_contents('php://input') to retrieve it
  // b) via FormData where data is sent in the $_FILES[] and $_POST[] arrays
  
if (count($_POST) === 0) {
  // no files to upload
  $data = json_decode(file_get_contents('php://input'), true);
} else {
  // files to upload, via FormData
  $data = $_POST;
}

$errmsg = null;
require_once('iErrorHandling.php');

$result = array('value' => 'success');
$res = true;   // sometimes $res is not used

$todo = $data['todo'];

if ($todo === 'phpInit') {
  // goto be;  // for testing
  // this is outside a try{} block because $errLog and $pLog have not yet been set
  $relDocPath = $data['relDocPath'];  // must be set else error
  $fullDocPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $relDocPath;
  $tmpPath = $fullDocPath . 'tmp/';
  // $uploadPath = $fullDocPath . 'upload/';
  // // $editedPath = $fullDocPath . 'edited/';
  if (!is_dir($fullDocPath)) mkdir($fullDocPath,0755);    // /docs/
  if (!is_dir($tmpPath)) mkdir($tmpPath,0755);        // /tmp/
  // if (!is_dir($uploadPath)) mkdir($uploadPath,0755);        // /upload/
  // if (!is_dir($editedPath)) mkdir($editedPath,0755);        // /edited/
  // set the log files
  $log = new Log($tmpPath,'error','errLog');
  // $log = new Log($tmpPath,'progress','pLog');
  goto goodExit;
}
    
try {
  if (isset($data['iFile'])) $iFile = ($data['iFile']); else $iFile = 'none';
  if ($iFile === 'none'){

    switch( $todo) {
  
      case 'openfile':
        $_SESSION['sfpath'] = urldecode($data['fpath']);
        $fpath = $_SERVER['DOCUMENT_ROOT'] . '/' . $_SESSION['sfpath'];
        if (!file_exists($fpath)) {
          $errmsg="$fpath does not exist";
          goto be;
        }
        require_once 'fileClass.php';
        $mf = new fileClass($fpath);
        
        $_SESSION['fpath'] = $fpath;  
        $_SESSION['userin'] = false;
    
        $mf->getVars($result['ansCoder'],$result['numCats'],$result['subject'],$result['isRestricted'],
          $result['nFreeAccessQs'],$result['base'],$result['fdata'],$result['totalQs']);
        if ($result['isRestricted']) {
          $stored = $data['stored'];   // localStorage
          //$result['pointReached'] = 0;
          $ss = str_replace([" ","/",".","\\"], "_", $_SESSION['sfpath']);
          if (isset($_SESSION[$ss])) {
            if ($_SESSION[$ss] === $_SESSION['compID']) {
              $result['isRestricted'] = false;
              //$result['pointReached'] = 1;
            }
          } else if ($stored != null) {
            if ($stored === $_SESSION['compID']) {
              $_SESSION[$ss] = $_SESSION['compID'];
              $result['isRestricted'] = false;
              //$result['pointReached'] = 2;
            }
          } else if (isset($_COOKIE[$ss])) {
            if ($_COOKIE[$ss] === $_SESSION['compID']) {
              $_SESSION[$ss] = $_SESSION['compID'];
              $result['isRestricted'] = false;
              //$result['pointReached'] = 3;
            }
          }
        }
          
        //if ($result['isRestricted']) {
          $result['codeBytes'] = $mf->getcodeBytes();  // a 4 byte array
          $result['dcontact'] = $mf->getdcontact();
        //} else {
          //$result['codeBytes'] = "";
          //$result['dcontact'] = "";
        //}
        $result['cdArr'] = $mf->getCdArray();  // the code array used in switchDown
        $result['ptrArr'] = $mf->getPtrArray();
        $result['catArr'] = $mf->createCatArray();
        $result['settings'] = $mf->getSettingsArray();
        $result['filesPath'] = $mf->filesPath;
        $mf->getFontVars($result['textColor'],$result['fontName'],$result['fontSize'],
                        $result['fontBold'],$result['fontUnderline'],$result['fontItalic']);
        $mf->getFrameVars($result['FrameBorderWidth'],$result['FrameBackColor'],$result['FrameBorderColor'],
                        $result['RimWidth']);
        $mf->getBackgroundVars($result['backgroundPicture'],$result['backgroundColor'],$result['backgroundStyle'],
                                $result['backgroundSound'],$result['backQTimeSound']);
        $mf->getKeyFenceTickVars($result['keyForecolor'],$result['keyBackcolor'],$result['keyBordercolor'],
                                $result['fenceTLcolor'],$result['fenceBRcolor'],$result['tickColor']);
        $result['pwString'] = $mf->getPwString();
        break;
        
      // *****************************************************  
      case 'setFree':
        // This sets a SESSION value for a restricted-access file that has been registered
        // It also sets the cookie value if $doCookie = 1
        $doCookie = $data['doCookie'];
        $ss = str_replace([" ","/",".","\\"], "_", $_SESSION['sfpath']);
        if ($doCookie == 1) {
          //$result['docookie'] = $doCookie;
          //setcookie($ss, $_SESSION['compID'], time() + 5*60);   // set for 5 minutes for testing
          setcookie($ss, $_SESSION['compID'], time() + 10*365*24*60*60);   // set for 10 years
        }
        $_SESSION[$ss] = $_SESSION['compID'];
        break;
        
      // *****************************************************  
      case 'setCompID':
        // strpos();   // for testing
    
        // this sets $_COOKIE['compID'] and $_SESSION['compID'] which is needed to unlock restricted-access files
        if (!isset($_COOKIE['compID'])) {
          //echo 'the cookie was not set';
          $compID = mt_rand(68000000,675999000);       // 26x26x100591 to 26x26x999998
        } else {
          //echo 'the cookie was set';
          $compID = $_COOKIE['compID'];
        }
        // always set/reset the compID cookie
        setcookie('compID',$compID,time() + 10*365*24*60*60);    // 10 years, check if 10 works, seems ok
        $_SESSION['compID'] = $compID;
          // setcookie('compID','',time() - 2*60);     // deletes the existing cookie
        $result['compID'] = $compID;
        break;
        
      // *****************************************************  
      case 'setSessionCompID':
      // exit('rubbish and make this long to see the error that is returned and then even longer');
      // )
      // abc;
      // strpos();
        // this sets $_SESSION['compID'] which is needed to unlock restricted-access files
        if (isset($data['compID'])) {
          $_SESSION['compID'] = urldecode($data['compID']);
        }  
        break;
        
      // *****************************************************  
      /*   case 'testing':
        // 
        $ss = preg_replace('/[ -\/ \\ ]/', "_", 'a b-c/d\e');
          $ss = str_replace([" ","/",".","\\"], "_", 'a b-c/d\e');
        $result['test'] = $ss;
        break;
      */ 
        
      case 'xxx':
        break;

    }   // end of switch
    
  } else if ($iFile === 'other') {
    
    switch( $todo) {
      case 'something':
        break;

      default:   // if $todo is not found then an error has occurred
        goto be;
    }   // end of switch


  } else if ($iFile === 'myExpl') {
    // code that needs iExpl.php
    require_once('iExpl.php');

    switch( $todo) {
      case 'getFolders':
      case 'getFiles':
        if ('getFolders' === $todo){
          $notdirs = "";
          if (isset($data['notdirs'])) $notdirs = urldecode($data['notdirs']);
          getFolders($data['dir'],$notdirs,$result['f'],$result['pn'],$result['ps']);
        } else if ('getFiles' === $todo){
          $only = "";
          if (isset($data['only'])) $only = urldecode($data['only']);
          getFiles($data['dir'],$only,$result['files']);
        }
        break;

      default:   // if $todo is not found then an error has occurred
        goto be;
        
    }
  }

  $result['res'] = $res;
  if ($res === false) goto be;
  if (isset($errmsg[0])) {
    $result['errmsg'] = $errmsg[0];
  }

} catch (Exception $e){
  $errmsg[1] = 'From fr.php: ' . errMessage($e);
  goto be;
}


goodExit:
  if (isset($f1)) unset($f1);
  echo json_encode($result); 
  exit;

be:   // bad exit
  if (isset($f1)) unset($f1);
  $result['value'] = 'fail';
  if (isset($errmsg)) {
    if (isset($errmsg[1]))$result['errmsg'] = $errmsg[1]; else $result['errmsg'] = $errmsg;
  } else {
    $result['errmsg'] = 'Unknown error';
  }
  $errLog = unserialize($_SESSION['errLog']);
  $errLog->write('## ' . date("H:i") . ': Caught exception in fr.php ##');
  $errLog->write('$todo=' . $todo);
  $errLog->write($result['errmsg']);
  echo json_encode($result); 
  exit;

