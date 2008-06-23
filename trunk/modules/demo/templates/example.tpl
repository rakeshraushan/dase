<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>
			{block name="title"}DASe: Digital Archive Services{/block}
		</title>
		<style type="text/css">
			{block name="style"}{/block}
		</style>
		<meta name="description" content="
		The Digital Archive Services project 
		is a lightweight digital content repository
		created by the College of Liberal Arts at 
		The University of Texas at Austin."/>

		<base href="{$app_root}modules/sandbox/"/>

		<link rel="stylesheet" type="text/css" href="www/css/yui.css"/>
		<link rel="stylesheet" type="text/css" href="www/css/style.css"/>
		<link rel="stylesheet" type="text/css" href="www/css/menu.css"/>
		<link rel="shortcut icon" href="www/images/favicon.ico"/>

		<!--[if lt IE 7]>
		<script type="text/javascript" src="scripts/ie7/IE7.js"></script>
		<script type="text/javascript" src="scripts/ie7/ie7-squish.js"></script>
		<script type="text/javascript" src="scripts/ie7/ie7-recalc.js"></script>
		<![endif]-->
		<script type="text/javascript" src="www/scripts/http.js"></script>
		<script type="text/javascript" src="www/scripts/json2.js"></script>
		<script type="text/javascript" src="www/scripts/dase.js"></script>

		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="css/ie.css"/>
		<![endif]-->

	</head>
  <body>
	<div id="skipnav"><a href="#maincontent" title="Skip to main content">Skip to main content</a></div>

	<noscript>
	  <h1 class="alert">This page requires that JavaScript be turned on.</h1>
	</noscript>


	<div id="logoffControl" class="login controls hide">
	  <div id="userName"> is logged in. (<a href="logoff" id="logoff-link" class="logoff">logoff</a>)</div>
	  <div id="eid" class="hide"></div>
	</div>

	<div id="loginControl" class="login controls hide">
	  <div>Got a UT EID? <a href="login" class="login">login!</a></div>
	</div>

	<div id="header">
	  <a href="http://www.utexas.edu"><img src="www/images/wordmark.jpg" alt="college of liberal arts logo"/></a>
	</div>

	<div id="upper">
	  <insert-header/>
	</div>

	<div id="center-content">
	  <insert-content/>
	</div>

	<div id="footer">
	  <a href="apps/help" id="helpModule">FAQ</a> | 
	  <a href="mailto:dase@mail.laits.utexas.edu">email</a> | 
	  <a href="copyright">Copyright/Usage Statement</a>| 
	  <insert-timer/> seconds |
	  <img src="www/images/dasepowered.png" alt="DASePowered icon"/>
	</div>
	<div id="debugData" class="hide"></div>
	<div id="pageHook" class="hide"><insert-page-hook/></div>
  </body>
</html>
