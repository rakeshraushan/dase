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

		<base href="{$app_root}"/>

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
		<script type="text/javascript" src="www/scripts/trimpath/template.js"></script>
		<script type="text/javascript" src="www/scripts/dase/{block name="js_include"}default.js{/block}"></script>
		<script>
			{block name="javascript"}
			//alert('hi from block');
			{/block}
		</script>


		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="css/ie.css"/>
		<![endif]-->

		{if $feed_url}
		<link rel="alternate" type="application/atom+xml" href="{$feed_url}"/>
		{/if}

		{if $json_url}
		<link rel="alternate" type="application/json" href="{$json_url}"/>
		<script type="text/javascript" src="www/scripts/dase/slideshow.js"></script>
		{/if}

	</head>

	<body>
		<div id="skipnav"><a href="#maincontent" title="Skip to main content">Skip to main content</a></div>

		<noscript>
			<h1 class="alert">The optimal DASe experience requires Javascript!</h1>
		</noscript>


		<div id="top">
			<div id="logoffControl" class="login hide">
				logged in as <span id="userName"></span> 
				|
				<a href="settings" class="edit" id="settings-link">settings</a>
				|
				<a href="help" class="edit">help</a>
				|
				<a href="logoff" class="edit" id="logoff-link">logout</a>
				<div id="eid" class="pagedata"></div>
			</div>

			<div id="loginControl" class="login hide">
				<div>Got a UT EID? <a href="login/form" class="alert">login!</a></div>
			</div>

			<div id="wordmark">
				<a href="http://www.utexas.edu"><img src="www/images/UTwordmark_02.jpg" alt="ut logo"/></a>
			</div>
		</div>

		<div id="header">
			<!-- background image here-->	
		</div>

		<div id="sidebar">
			<ul id="menu">
				<li id="home-menu"><a href="" class="main">Home/Search</a></li>
				<li id="cart-menu"><a href="" class="main" id="cartLabel">My Cart</a>
				<ul class="hide" id="cart-submenu">
					<li><a href="" class="create" id="cartLink">view cart</a></li>
					<li><a href="" class="create">empty cart</a></li>
					<!--
					<li><a href="" class="create" id="moveCartTo">move cart items to...</a>
					<ul class="hide">
						<li>
						<form action="sss" method="post">
							<div id="allTags">
							</div>
							<div><input type="submit" value="move items"/></div>
						</form>
						</li>
					</ul>
					</li>
					-->
				</ul>
				</li>
				<!--
				<li id="shared-menu"><a href="" class="main">Shared Collections &amp; Slideshows</a>
				<ul class="hide" id="shared-submenu">
					<li class="placeholder"></li>
				</ul>
				</li>
				-->

				<li id="user_collection-menu"><a href="" class="main">My Collections</a>
				<ul class="hide" id="user_collection-submenu">
					<li class="placeholder"></li>
				</ul>
				</li>

				<li id="slideshow-menu"><a href="" class="main">My Slideshows</a>
				<ul class="hide" id="slideshow-submenu">
					<li class="placeholder"></li>
				</ul>
				</li>

				<li id="subscription-menu"><a href="" class="main">My Subscriptions</a>
				<ul class="hide" id="subscription-submenu">
					<li class="placeholder"></li>
				</ul>
				</li>
			</ul>

			<h5 id="ajaxMsg"></h5>

		</div> <!-- closes sidebar -->

		<div id="content">
			<!-- accessibility -->
			<a id="maincontent" name="maincontent"></a>
			{block name="content"}default content{/block}
		</div>

		<div class="spacer"/>

			<div id="footer">
				<a href="admin" class="hide" id="adminLink"></a> |
				<a href="apps/help" id="helpModule">FAQ</a> | 
				<a href="mailto:dase@mail.laits.utexas.edu">email</a> | 
				<a href="copyright">Copyright/Usage Statement</a> | 
				<a href="manage" class="hide" id="manageLink"></a> 
				<!--
				<insert-timer/> seconds |
				-->
				<img src="www/images/dasepowered.png" alt="DASePowered icon"/>
			</div><!--closes footer-->
			<div id="debugData" class="pagedata"></div>
		</body>
	</html>
