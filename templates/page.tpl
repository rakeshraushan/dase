<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>

	<title>{$title}</title>
	<meta name="description" content="
	  The Digital Archive Services project 
	  is a lightweight digital content repository
	  created by the College of Liberal Arts at 
	  The University of Texas at Austin."/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<base href="{$app_root}/"></base>

	<link rel="stylesheet" type="text/css" href="css/style.css"/>
	<link rel="stylesheet" type="text/css" href="css/menu.css"/>
	<link rel="shortcut icon" href="{$app_root}/images/favicon.ico"/>

	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="css/ie.css"/>
	<![endif]-->

	<script type="text/javascript" src="scripts/dase.js"></script>
  </head>

  <body>
	<div id="skipnav"><a href="#content" title="Skip to main content">Skip to main content</a></div>

	<noscript>
	  <h1 class="alert">The optimal DASe experience requires Javascript!</h1>
	</noscript>


	<div id="logoffControl" class="login controls hide">
	  <div id="userName"> is logged in. (<a href="logoff" class="logoff">logoff</a>)</div>
	</div>

	<div id="loginControl" class="login controls hide">
	  <div>Got a UT EID? <a href="login" class="login">login!</a></div>
	</div>

	<div id="wordmark">
	  <a href="http://www.utexas.edu"><img src="images/UTwordmark_02.jpg" alt="ut logo"/></a>
	</div>

	<div class="daseBanner"></div>
	<div id="sidebar">

	  <ul id="menu">
		<li class="home"><a href="" class="main">Home/Search</a></li>
		<li class="cart"><a href="" class="main">My Cart</a>
		  <ul class="hide" id="cart">
			<li><a href="" class="create">view cart</a></li>
			<li><a href="" class="create">empty cart</a></li>
			<li><a href="" class="create" id="moveCartTo">move cart items to...</a>
			  <ul class="hide">
				<li>
				  <form action="sss" method="post">
					<div id="tagsSelect">
					  <!-- ajax fills in here -->
					</div>
					<div><input type="submit" value="move items"/></div>
					</form>
				  </li>
				</ul>
			  </li>

			</ul>
		  </li>

		  <li class="user_collection"><a href="" class="main">My Collections</a>
			<ul class="hide" id="user_collection">
			  <li class="menuForm">
				<form action="sss" method="post">
				  <div><input type="text" name="coll_name"/></div>
				  <div><input type="submit" value="create collection"/></div>
				</form>
			  </li>
			  <!-- ajax fills in here -->
			</ul>
		  </li>

		  <li class="slideshow"><a href="" class="main">My Slideshows</a>
			<ul class="hide" id="slideshow">
			  <li class="placeholder"></li>
			</ul>
		  </li>

		  <li class="subscription"><a href="" class="main">My Subscriptions</a>
			<ul class="hide" id="subscription">
			  <li class="placeholder"></li>
			</ul>
		  </li>
		</ul>

		<div class="loadingMsg" id="ajaxMenuMsg"></div>

	  </div> <!-- closes sidebar -->

{include file="content/$content.tpl"}

	  <div class="footer">
		<a href="{$app_root}/manage" class="hide">manage DASe</a> | 
		<a href="apps/help" id="helpModule">FAQ</a> | 
		<a href="mailto:dase@mail.laits.utexas.edu">email</a> | 
		<a href="copyright">Copyright/Usage Statement</a>| 
		{$timer} seconds |
		<img src="images/dasepowered.png" alt="DASePowered icon"/>
	  </div><!--closes footer-->
	</body>
  </html>
