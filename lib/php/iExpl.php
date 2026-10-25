<?php

function getFolders($dir,$notdirs,&$f,&$pn,&$ps){
  $f = array(0 => "rootDir");
  $pn = array(0 => 0);
  $ps = array(0 => 0);
  $nexti = 1;
  $fpath = $_SERVER['DOCUMENT_ROOT'] . '/' . $dir;
  // used to be function doFolder3()
  $stack1[] = $fpath;
  $stack2[] = 0;
  while ($stack1) {
    $folder = array_pop($stack1);
    $fi = array_pop($stack2);
    $subs = [];
    $n = 0;
    $files = scandir($folder);
    // natcasesort($files);
    if( count($files) > 2 ) { /* The first 2 files are . and .. */
      $files = array_diff($files, Array( ".", ".." ));
      foreach( $files as $file ) {
        if (is_dir($folder.$file)){
          if ($notdirs === '' || strpos($file,$notdirs) === false) {
            $subs[] = $file;
            $n++;
          }
        }
      }
    }
    // sort the folders and fill in the $f[], $pn[] and $ps[] arrays
    if ($n > 0) {
      // if natcasesort is here it has less to sort but I must use foreach instead of a for loop because natcasesort moves the indexes as well which defeats the purpose
      natcasesort($subs);
      $ps[$fi] = $nexti;
      $i = 0;
      foreach($subs as $sub){
        $np = $nexti + $i;
        $f[$np] = $sub;
        $pn[$np] = $np + 1;
        $ps[$np] = 0;
        $stack1[] = $folder. $sub . '/';
        $stack2[] = $np;
        $i++;
      }
      $nexti = $nexti + $n;
      $pn[$nexti-1] = 0;  // reset last in chain to 0
    }
  }
}


function getFiles($dir,$only,&$filesArr){
  $fpath = $_SERVER['DOCUMENT_ROOT'] . '/' . $dir;
  $filesArr = [];
  if( file_exists($fpath) ) {
    $files = scandir($fpath);
    natcasesort($files);
    if( count($files) > 2 ) { /* The first 2 files are . and .. */
      foreach( $files as $file ) {
        if(file_exists($fpath.$file) && $file != '.' && $file != '..') {
          // only look for files and not directories
          if (!is_dir($fpath.$file)){
            $ext = strtolower(preg_replace('/^.*\./', '', $file));
            if ($only === "" || strpos($only,'|'.$ext.'|') !== false) {
              $filesArr[] = $file;
              $filesArr[] = $ext;
            }
          }
        }
      }
    }
  }
}


