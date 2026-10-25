
<?php
define('LEN_MAIN_SECTOR',4096);
define('LEN_PTR_SECTOR',512);


/* 
Private Type mainSectorType     ' 4096 bytes
  version(1 To 2) As Byte            ' 2, 0
  mpm(1 To 3) As Byte                ' 3, 2
  subject As stringPtrType           ' 5, 5
  cat(1 To cats) As stringPtrType    ' 500 x 5 = 2500,  10
  numPtrSectors As Integer           ' 2, 2510
  ansCoder As Byte                   ' 1, 2512
  numCats As Integer                 ' 2, 2513
  perCat(0 To cats) As Integer       ' 501 x 2 = 1002, 2515
  totalQs As Integer                 ' 2, 3517
  Ds As Integer                      ' 2, 3519
  Zs As Integer                      ' 2, 3521
  isRestricted As Boolean            ' 2, 3523
  codeBytes(1 To 4) As Byte          ' 4, 3525
  dContact(1 To 6) As myString25     ' 156, ie 6 x 26, 3529
  nFreeAccessQs As Integer           ' 2,  3685
  codeCheck As Integer               ' 2,  3687
  backPictureFile As stringPtrType2  ' 6,  3689
  ' not used in 903, except to copy to new text colors
  DPicForecolor As Long              ' 4,  3695
  '-----------------------
  DPicBackcolor As Long              ' 4,  3699
  keyForecolor As Long             '4,  3703
  keyBackcolor As Long             '4,  3707
  keySurroundcolor As Long         '4,  3711
  fenceTLcolor As Long             '4,  3715
  fenceBRcolor As Long             '4,  3719
  tickColor As Long                '4,  3723
  allowPrintingQList  As Boolean     ' 2,  3727
  backSoundFile As stringPtrType2    ' 6,  3729
  backQTimeSoundFile As stringPtrType2    ' 6,  3735
  ' not used in 903, except to copy to new fonts
  oldfontName As stringPtrType2  ' 6,  3741
  oldfontSize As Integer         ' 2,  3747
  oldfontBold As Boolean         ' 2,  3749
  oldfontUnderline As Boolean    ' 2,  3751
  oldfontItalic As Boolean       ' 2,  3753
  '------------
  Q_PIC_W(0 To nPF) As Integer        ' 11 x 2 = 22, 3755
  Q_PIC_H(0 To nPF) As Integer        ' 11 x 2 = 22
  A_PIC_W(0 To nPF) As Integer        ' 11 x 2 = 22
  A_PIC_H(0 To nPF) As Integer        ' 11 x 2 = 22
  E_PIC_W(0 To nPF) As Integer        ' 11 x 2 = 22
  E_PIC_H(0 To nPF) As Integer        ' 11 x 2 = 22
  nDontFit As Integer            '2,  3887
  ' 902
  ptrFilePath As stringPtrType2    ' 6,  3889
  ' 903
  TextColor(1 To 2) As Long        ' 4 x 2,  3895
  fontName(1 To 2) As stringPtrType2  ' 6 x 2,  3903
  fontSize(1 To 2) As Integer         ' 2 x 2,  3915
  fontBold(1 To 2) As Boolean         ' 2 x 2,  3919
  fontUnderline(1 To 2) As Boolean    ' 2 x 2,  3923
  fontItalic(1 To 2) As Boolean       ' 2 x 2,  3927
  FrameBorderWidth(1 To 2) As Integer      ' 2 x 2,  3931
  FrameBackColor(1 To 2) As Long     ' 4 x 2,  3935
  FrameBorderColor(1 To 2) As Long   ' 4 x 2,  3943
  RimWidth(1 To 2) As Integer        ' 2 x 2,  3951
  ' 906
  numTopScorers As Byte    ' 1,  3955
  topScorer(1 To 5) As stringPtrType2    ' 5 x 6
  vSoundFile As stringPtrType2    ' 6
  ' 908
  filepathAbsolute As Boolean     '2,  3992
  ' 1002
  windowW As Integer              ' 2
  windowH As Integer              ' 2
  ' 1003
  filesRef As Integer  ' 2  | 0=rel,abs or FilePath, 1=abs only,  2=FilePath only
  ' 1004
  PWString As stringPtrType2       ' 6   4000
  backgroundStyle As Byte          ' 1   4006 
  ' extra bytes to fill up mainsector
  xBytes(1 To 89) As Byte           ' 89
End Type
 */

//**********************************************************************************
//************************************ fileClass ***********************************
//**********************************************************************************

class fileClass {
  private $filename;
  public $filesPath;
  private $fsize;
  private $vers1, $vers2;
  private $numPtrSectors;
  private $ansCoder;
  private $numCats;
  private $cats;     // array of categories
  private $subject;
  private $totalQs;
  private $Ds;
  private $Zs;
  private $isRestricted;
  private $nFreeAccessQs;
  private $codeBytes;
  private $cd;
  private $ptrs;     // array of pointers to questions in the file
  private $base;
  private $settings;  // 0 to 11 settings for width & height of images
  private $fdata;
  
  private $textColor;
  private $fontName;
  private $fontSize;
  private $fontBold;
  private $fontUnderline;
  private $fontItalic;
  
  private $FrameBorderWidth;
  private $FrameBackColor;
  private $FrameBorderColor;
  private $RimWidth;
  
  private $backgroundPicture;
  private $backgroundColor;
  private $backgroundStyle;
  private $backgroundSound;
  private $backQTimeSound;
  
  
  
  private $keyForecolor;
  private $keyBackcolor;
  private $keyBordercolor;
  private $fenceTLcolor;
  private $fenceBRcolor;
  private $tickColor;
  private $pwString;
  
  private $dcontact;
  

function __construct($name) {
  $this->filename = $name;
  // check the file size
  $this->fsize = filesize($this->filename);
  if ($this->fsize <= LEN_MAIN_SECTOR) die('file defective');
  // calculate the file path to the _files folder connected to this qna file
  $pos = strrpos($name,'.');
  $this->filesPath = substr_replace($name,'_files',$pos);
  // initialize file variables
  $this->init();
}

//***** For pack and unpack the following apply
// C is 8 bit unsigned character
// A is space added string, A3 means get 3 bytes
// V is 32 bit unsigned, little Endian, same as VB5's Long except it is unsigned
// v is 16 bit unsigned, little Endian, same as VB5's Integer except it is unsigned

// Note that unpack returns an object, [1] is the first element

function init() {
  // open file and read its contents
  $fh = fopen($this->filename, "rb");
  $contents = stream_get_contents($fh);
  //$contents = fread($fh, $this->fsize);
  fclose($fh);
  
  // get these variables first because I need $numPtrSectors and $numCats to get others
  $format = 'vv1/Cv2/vv3/';
  $ta = unpack($format,substr($contents,2510,5)); 
  $this->numPtrSectors = $ta['v1'];
  $this->ansCoder = $ta['v2'];
  $this->numCats = $ta['v3'];
  if ($this->numCats > 500) die('file defective - too many cats');
  
  // set the base now because we have numPtrSectors
  $this->base = LEN_MAIN_SECTOR + $this->numPtrSectors * LEN_PTR_SECTOR - 1;
  $this->fdata = unpack('C*',substr($contents,$this->base));
  
  $ta = unpack('v4',substr($contents,3517,8)); 
  
  $this->totalQs = $ta[1];
  $Ds = $ta[2];
  $Zs = $ta[3];
  $this->isRestricted = ($ta[4] !== 0);
  
  $ta = unpack('v1',substr($contents,3685,2)); 
  $this->nFreeAccessQs = $ta[1];
  
  $this->codeBytes = unpack('C4',substr($contents,3525,4));
  $this->init4Coding($this->codeBytes,  $this->isRestricted, $this->cd);

  $format = 'Cv1/Cv2/A3name/Vsubject_off/Csubject_len/';
  $ta = unpack($format,$contents); 
  $this->vers1 = $ta['v1'];
  $this->vers2 = $ta['v2'];
  if ($ta['name'] !== 'mpm') die('wrong mpm id');
  $this->subject = substr($contents,$ta['subject_off']+$this->base,$ta['subject_len']);
  
  $ss = substr($contents,10,2500);
  for ($i = 0;  $i < $this->numCats; $i++) {
    $off = $i * 5;
    $s = substr($ss,$off,5);
    $ta = unpack('Vcat_off/Ccat_len',$s);
    if ($ta['cat_len'] == 0) break;
    $this->cats[$i+1] = substr($contents,$ta['cat_off']+$this->base,$ta['cat_len']);
  } 
  
  // this code makes the pointer array
  $nA = 0;
  for ($i = 1; $i <= $this->numPtrSectors; $i++) {
    $sector = substr($contents,LEN_MAIN_SECTOR+($i-1)*LEN_PTR_SECTOR,LEN_PTR_SECTOR);
    // read the first byte which is the no of ptrs in the sector
    $ta = unpack('c1',$sector);
    $nPtrs = $ta[1];
    for ($j = 0; $j < $nPtrs; $j++) {
      $ta = unpack('v1cat/V1off/v1len',substr($sector,1+$j*8,8));
      $this->ptrs[++$nA]['cat'] = $ta['cat'];
      $this->ptrs[$nA]['off'] = $ta['off'];
      $this->ptrs[$nA]['len'] = $ta['len'];
    }
  }
  if ($this->totalQs !== $nA) {
    //echo 'error $this->totalQs !== $nA';
  }
  
  // get the number of qs per category in perCat[]  
  $perCat = unpack('v*',substr($contents,2517,$this->numCats*2));
  $ta = unpack('v1',substr($contents,2515,2));
  $perCat[0] = $ta[1];
      
  // get the settings[] array 
  $tmp = unpack('v*',substr($contents,3755,132));
  $n = 1;
  for ($i = 0; $i < 11; $i++) {
    $this->settings['Q_W'][$i] = $tmp[$n++];
  }
  for ($i = 0; $i < 11; $i++) {
    $this->settings['Q_H'][$i] = $tmp[$n++];
  }
  for ($i = 0; $i < 11; $i++) {
    $this->settings['A_W'][$i] = $tmp[$n++];
  }
  for ($i = 0; $i < 11; $i++) {
    $this->settings['A_H'][$i] = $tmp[$n++];
  }
  for ($i = 0; $i < 11; $i++) {
    $this->settings['E_W'][$i] = $tmp[$n++];
  }
  for ($i = 0; $i < 11; $i++) {
    $this->settings['E_H'][$i] = $tmp[$n++];
  }
  
  // get the color and font of the text   
  $this->textColor = unpack('V2',substr($contents,3895,8));
  $ta[1] = unpack('Voff/vlen',substr($contents,3903,6));
  $ta[2] = unpack('Voff/vlen',substr($contents,3909,6));
  $this->fontName[1] = substr($contents,$ta[1]['off']+$this->base,$ta[1]['len']);
  $this->fontName[2] = substr($contents,$ta[2]['off']+$this->base,$ta[2]['len']);
  $this->fontSize = unpack('v2',substr($contents,3915,4));
  $this->fontBold = unpack('v2',substr($contents,3919,4));
  $this->fontUnderline = unpack('v2',substr($contents,3923,4));
  $this->fontItalic = unpack('v2',substr($contents,3927,4));
    
  // get the Frame characteristics  
  $this->FrameBorderWidth = unpack('v2',substr($contents,3931,4));
  $this->FrameBackColor = unpack('V2',substr($contents,3935,8));
  $this->FrameBorderColor = unpack('V2',substr($contents,3943,8));
  $this->RimWidth = unpack('v2',substr($contents,3951,4));
    
  // get the background picture and color and style
  $ta = unpack('Voff/vlen',substr($contents,3689,6));
  $this->backgroundPicture = substr($contents,$ta['off']+$this->base,$ta['len']);
  $this->backgroundColor = unpack('V',substr($contents,3699,4))[1];  // index 1 of the object
  $ta = unpack('Cv',substr($contents,4006,1)); 
  $this->backgroundStyle = $ta['v'];
  
  // get the background sound and QTime sound
  $ta = unpack('Voff/vlen',substr($contents,3729,6));
  $this->backgroundSound = substr($contents,$ta['off']+$this->base,$ta['len']);
  $ta = unpack('Voff/vlen',substr($contents,3735,6));
  $this->backQTimeSound = substr($contents,$ta['off']+$this->base,$ta['len']);
  
  // get key, fence and tick color
  $ta = unpack('V6',substr($contents,3703,24));
  $this->keyForecolor = $ta[1];
  $this->keyBackcolor = $ta[2];
  $this->keyBordercolor = $ta[3];
  $this->fenceTLcolor = $ta[4];
  $this->fenceBRcolor = $ta[5];
  $this->tickColor = $ta[6];
  
  // get the pwString
  $ta = unpack('Voff/vlen',substr($contents,4000,6));
  $this->pwString = substr($contents,$ta['off']+$this->base,$ta['len']);
  
  // get the dcontact
  for ($i = 0; $i < 6; $i++) {
    $k = 3529 + $i*26;
    $ta = unpack('C1',substr($contents,$k,1));
    $ta = unpack("A$ta[1]",substr($contents,$k+1));
    $this->dcontact[$i+1] = $ta[1];
  }

}

private function init4Coding($b,  $encrypt, &$cd) {
  // ' 1) sets the cd() array values low,high and sum
  // ' 2) it is used by QnA and editQnA
  // ' 3) it is used to encrypt and decrypt Qs and As
  // '    in a qna file; if the file is a restricted-
  // '    access file then b(3) and b(4) are used in
  // '    addition to b(1) and b(2) which are always used
  if ($encrypt) { $b13 = $b[1] + $b[3];} else {$b13 = $b[1];};
  if ($encrypt) { $b24 = $b[2] + $b[4];} else {$b24 = $b[2];};
  $cd['low'] = 50 + $b13 % 25;
  $cd['high'] = 105 + $b24 % 18;
  $cd['sum1'] = $cd['low'] + $cd['high'];
}

public function getVars(&$ansCoder,&$numCats,&$subject,&$isRestricted,&$nFreeAccessQs,&$base,&$fdata,&$totalQs) {
  $ansCoder = $this->ansCoder;
  $numCats = $this->numCats;
  $subject = $this->subject;
  $isRestricted = $this->isRestricted;
  $nFreeAccessQs = $this->nFreeAccessQs;
  $base = $this->base;
  $fdata = $this->fdata;
  $totalQs = $this->totalQs;
}

public function getCdArray() {
  return $this->cd;
}

public function getcodeBytes() {
  return $this->codeBytes;
}

public function getPtrArray() {
  return $this->ptrs;
}

public function getSettingsArray() {
  return $this->settings;
}

public function getFontVars(&$textColor,&$fontName,&$fontSize,&$fontBold,&$fontUnderline,&$fontItalic) {
  $textColor = $this->textColor;
  $fontName = $this->fontName;
  $fontSize = $this->fontSize;
  $fontBold = $this->fontBold;
  $fontUnderline = $this->fontUnderline;
  $fontItalic = $this->fontItalic;
}

public function getFrameVars(&$FrameBorderWidth,&$FrameBackColor,&$FrameBorderColor,&$RimWidth) {
  for ($i = 1; $i<=2; $i++) {
    if ($this->FrameBorderWidth[$i] > 32767) {
      $FrameBorderWidth[$i] = $this->FrameBorderWidth[$i] - 65536;
    } else {
      $FrameBorderWidth[$i] = $this->FrameBorderWidth[$i];
    }
  }
  $FrameBackColor = $this->FrameBackColor;
  $FrameBorderColor = $this->FrameBorderColor;
  $RimWidth = $this->RimWidth;
}

public function getBackgroundVars(&$backgroundPicture,&$backgroundColor,&$backgroundStyle,
  &$backgroundSound,&$backQTimeSound) {
  // background picture
  $backgroundPicture = $this->backgroundPicture;
  $backgroundColor = $this->backgroundColor;
  $backgroundStyle = $this->backgroundStyle;
  // background sound and QTime sound
  $backgroundSound = $this->backgroundSound;
  $backQTimeSound = $this->backQTimeSound;
}

public function getKeyFenceTickVars(&$keyForecolor,&$keyBackcolor,&$keyBordercolor,&$fenceTLcolor,
                                    &$fenceBRcolor,&$tickColor) {
  $keyForecolor = $this->keyForecolor;
  $keyBackcolor = $this->keyBackcolor;
  $keyBordercolor = $this->keyBordercolor;
  $fenceTLcolor = $this->fenceTLcolor;
  $fenceBRcolor = $this->fenceBRcolor;
  $tickColor = $this->tickColor;
}

public function getPwString() {
  return $this->pwString;
}
  
public function createCatArray() {
  if ($this->numCats <= 0) return;
  for ($i = 1; $i <=  $this->numCats; $i++) {
  // ' set the initial values for name,n,selected
    $cat[$i]['name'] = $this->cats[$i];
    $cat[$i]['selected'] = true;
    $cat[$i]['n'] = 0;
    // setRangeFull i
  }
  // ' sort questions into the $cat array
  for ($i = 1; $i <= $this->totalQs; $i++) {
    $c = $this->ptrs[$i]['cat'];
    if ($c > 0) {    // $c === 0 means the question is marked as deleted
      $n = $cat[$c]['n'] + 1;
      $cat[$c]['n'] = $n;
      $cat[$c]['q'][$n] = $i;
    }
  }
  return $cat;
}

public function getdcontact() {
  return $this->dcontact;
}

}  // end of fileClass


//**********************************************************************************
//************************************ fileClass2 ***********************************
//**********************************************************************************

class fileClass2 {
  //private $filename;
  private $mainsector;

function __construct($name) {
  //$this->filename = $name;
  // open file and read its contents
  $fh = fopen($name, "rb");
  $this->mainsector = stream_get_contents($fh,LEN_MAIN_SECTOR);
  //$contents = fread($fh, $this->fsize);
  fclose($fh);
}

public function getdcontact(&$dcontact) {
  for ($i = 0; $i < 6; $i++) {
    $k = 3529 + $i*26;
    $ta = unpack('C1',substr($this->mainsector,$k,1));
    $ta = unpack("A$ta[1]",substr($this->mainsector,$k+1));
    $dcontact[$i+1] = $ta[1];
  }
}

// needed for checking creator login
// public function getPWString() {
// }

public function getcodebytes(&$codebytes) {
  $codebytes = unpack('C4',substr($this->mainsector,3525,4));
}

}  // end of fileClass2

?>