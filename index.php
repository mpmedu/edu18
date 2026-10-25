<?php
  session_start();  // nothing to come before this
  define('YR', date("Y"));
  define('SUBJECT', 'Home page');
?>
  

<!DOCTYPE html>
<html>
<head>
  <meta charset="iso-8859-1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  
  <meta name="keywords" content="Questions and answers, Multiple choice questions, quiz, 
                        Game, Multiple choice options, School tests, University tests, General knowledge,
                        Test yourself, The QnA site, The QnA website, Various subjects, Various topics,
                        TestU, TestU website, test you">
  <meta name="description" content="The QnA site, The QnA website, Questions and answers, Multiple choice questions, quiz, Game, Multiple choice options, School tests, University tests, General knowledge, Test yourself">
  <title>TestU edu18</title>
  <!-- 
  <link rel="stylesheet" href="../../lib/css/common.css">
  <link rel="stylesheet" href="../../lib/css/edu.css">
  <link rel="stylesheet" href="../../lib/css/edu.all.css">
  <link rel="stylesheet" href="lib/css/edu.all.css">
  <script src="../../lib/js/myNamespace2.js"></script>
  
 -->
 <link rel="stylesheet" href="http://localhost/common/common1.css">
 <!-- <link rel="stylesheet" href="./lib/css/common.css"> -->
 <link rel="stylesheet" href="http://localhost/common/myExpl.css">
 <!-- <link rel="stylesheet" href="./lib/css/myExpl.css"> -->
 <link rel="stylesheet" href="./lib/css/edu.css">


</head>

<!-- ****** body ****** -->
 
<body>

<div id='bodymask' class='nodisplay'></div>   <!-- must be first div in body -->

<!-- displays when the page is loading -->
<div id = 'eggDiv'>
  <svg width='400px' height='200px'>
    <ellipse id="egg" cx="200" cy="100" rx="180" ry="90" />
    <text x='50' y='108' font-size='20px' fill='white'>Please wait while the page loads...</text>
  </svg>
</div>
 
  <style></style>
  <script>
    // this code disables the back button
    history.pushState(null, document.title, location.href);
    window.addEventListener('popstate', function (event)  {
      history.pushState(null, document.title, location.href);
    });  
  </script>
  

<!--[if lt IE 7]>
<p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
<![endif]-->

  

	<div id="topdiv">
    <div id="topdivLeft">
      <p id="debug" class="nodisplay">D</p>
      <p id="siteName"></p>
      <p id="pageName"></p>
    </div>
    
    <div id="topdivCenter">
      <div id="scorecontainer" class='nodisplay'>
        <p id="numQs">Questions in test &nbsp;<input id="score_input" type="text" disabled="true"></p>
        <p style="text-align:center;margin-top:6px;"><span id="thescore" >0/0</span></p>
      </div>
    </div>

    <div id="topdivRight">
      <div id="topmenucontainer">
        <div id='topmenumask'></div>
        <div id='toprightmenumask'></div>
        <div id = 'mainmenu_placeholder'></div>
        <p id="fn">Filename  <input id="fn_output" type="text" disabled="true"></p>
      </div>
      <div id="gamecontainer">
        <div id='gobuttonmask'></div>
        <button id="gobutton">New game</button>
        <p><input id="clock" type="text" disabled="true"> </p>
      </div>
    </div>
    
	</div>    <!-- end topdiv -->
  
  <!-- 
  
 -->
  
  
  <noscript><h3 style="margin-left:2%;color:red">Javascript must be enabled to use this website</h3></noscript>
  
  
    <!-- wrapper contains the content, at start up it is empty, then init() puts the info, then the startup dialog is shown  -->
  <div id="wrapper">
  </div>
  <!-- wrappermask masks the wrapper when the game is played after a choice has been made  -->
  <div id='wrappermask' class='nodisplay'></div>
  
  <div id="bottomdiv">
    <br>
    <span id="Footer1_Copyright">Copyright <?php echo YR; ?>  All rights reserved.</span>  
    <br><br>
    <div style="position:absolute; top:7px; right:30px; text-align:center;">
      <p style="fontsize:8px;">Audio volume</p>
      <div id="cont" class="roundCorners">
        <div id="ball" class="roundCorners">
        </div>
      </div>
    </div>
  </div>
  
  
  <?php
  // the template files are put into this position
  require_once('http://localhost/common/tem_common1.php');
  require_once('http://localhost/common/tem_myExpl1.php');
  // require_once('./lib/htmlTemplates/tem_common.php');
  // require_once('./lib/htmlTemplates/tem_myExpl.php');
  require_once('./lib/htmlTemplates/tem_edu.php');
  ?>
  
  <!-- 
  
  <script src="./lib/js/myNamespace2.js"></script>
  <script src="./lib/js/qcommon5.js"></script>
  <script src="./lib/js/edu.js"></script>
  <script src="./lib/js/siteData.js"></script>
  <script src="./lib/js/myExpl2.js"></script>
  
  <script src="./lib/js/eduall.combined.adv.min.js"></script>
  
//-->

<script src="http://localhost/common/myNamespace2.js"></script>
<!-- <script src="./lib/js/myNamespace2.js"></script> -->
<script src="http://localhost/common/utils1.js"></script>

<script src="http://localhost/common/qcommon7.js"></script>
<!-- <script src="http://localhost/common/qcommon6.js"></script> -->
<!-- <script src="./lib/js/qcommon5.js"></script> -->
<!-- <script src="./lib/js/qcommon6.js"></script> -->

  <script src="./lib/js/edu.js"></script>
  <script src="./lib/js/siteData.js"></script>
  <!-- <script src="./lib/js/myExpl2.js"></script> -->
  <!-- <script src="./lib/js/myExpl5.js"></script> -->
  <!-- <script src="./lib/js/myExpl6.js"></script> -->
<script src="http://localhost/common/myExpl8.js"></script>
</body> 
</html>
   
