<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
		The University of Texas at Austin.">

		<base href="{$app_root}">

		<link rel="stylesheet" type="text/css" href="www/css/yui.css">
		<link rel="stylesheet" type="text/css" href="www/css/style.css">
		<link rel="stylesheet" type="text/css" href="www/css/menu.css">
		{block name="head-links"}{/block}
		{if $local_css}
		<link rel="stylesheet" type="text/css" href="{$local_css}">
		{/if}
		<link rel="shortcut icon" href="www/images/favicon.ico">

		<!-- atompub discovery -->
		<link rel="service" type="application/atomsvc+xml" href="service">
		{block name="servicedoc"}{/block}

		<script type="text/javascript" src="www/scripts/webtoolkit.base64.js"></script>
		<script type="text/javascript" src="www/scripts/http.js"></script>
		<script type="text/javascript" src="www/scripts/json2.js"></script>
		<script type="text/javascript" src="www/scripts/dase.js"></script>
		<script type="text/javascript" src="www/scripts/trimpath/template.js"></script>
		{block name="head"}{/block}


		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="css/ie.css">
		<![endif]-->

		{if $feed_url}
		<link rel="alternate" type="application/atom+xml" href="{$feed_url}">
		{/if}

		{if $json_url}
		<link rel="alternate" type="application/json" href="{$json_url}">
		{/if}

	</head>

	<body>
		<div id="skipnav"><a href="#maincontent" title="Skip to main content">Skip to main content</a></div>

		<noscript>
			<h1 class="alert">The optimal DASe experience requires Javascript!</h1>
		</noscript>

		<div id="logoffControl" class="login hide">
			<a href="settings" class="edit" id="settings-link"><span id="userName"></span></a> 
			|
			<a href="logoff" class="edit" id="logoff-link">logout</a>
			<div id="eid" class="pagedata"></div>
		</div>

		<div id="loginControl" class="login hide">
			<div>Got a UT EID? <a href="login/form" class="alert">login!</a></div>
		</div>

		{if $page_logo.src}
		<div id="pageLogo">
			<a href="{$page_logo.link_target}"><img src="{$page_logo.src}" alt="{$page_logo.alt}"></a>
		</div>
		{/if}

		<div id="header">
			{$main_title}
		</div>

		<div id="sidebar">
			<ul id="menu" class="hide">
				<li id="home-menu"><a href="" class="main">Home/Search</a></li>
				<li id="cart-menu"><a href="" class="main" id="cartLink">My Cart</a></li>
				<li id="sets-menu"><a href="" class="main">My Sets</a>
				<ul class="hide" id="sets-submenu">
					<li></li>
					<!-- insert javascript template output -->
				</ul>
				</li>
				<li id="settings-menu"><a href="settings" id="settings-menu-link" class="main">My Preferences</a>
				</li>
				<li id="manage-menu" class="hide"><a href="settings" id="manage-menu-link" class="main"></a>
				</li>
				{if $module_menu}
				{include file="$module_menu"}
				{/if}
			</ul>

			<ul id="menuGrayed">
				<li><a href="" class="main">Home/Search</a></li>
				<li><a href="" class="main">My Cart</a></li>
				<li><a href="" class="main">My Sets</a></li>
				<li><a href="" class="main">My Preferences</a></li>
			</ul>

			<textarea class="javascript_template" id="sets_jst">
				<li><a href='new' id='createNewSet' class='edit'>create new set</a></li>
				{literal}
				{for tag in tags}
				{if 'set' == tag.type || 'slideshow' == tag.type}
				<li>
				<a href='tag/${eid}/${tag.ascii_id}'>${tag.name} (${tag.count})</a>
				</li>
				{/if}
				{/for}
				{/literal}
			</textarea>

			<textarea class="javascript_template" id="saveto_jst">
				<select id='saveToSelect' name='collection_ascii_id'>
					<option value=''>save checked items to...</option>
					{literal}
					{for tag in tags}
					{if 'admin' != tag.type}
					<option value='${tag.ascii_id}'>${tag.name} (${tag.count})</option>
					{/if}
					{/for}
					{/literal}
				</select>
				<input type='submit' value='add'>
			</textarea>


			<h5 id="ajaxMsg" class="hide">loading...</h5>

		</div> <!-- closes sidebar -->

		<div id="content">
			<!-- accessibility -->
			<a id="maincontent" name="maincontent"></a>
			{block name="content"}default_content{/block}
		</div>

		<div class="spacer"></div>

			<div id="footer">
				<a href="manage" class="hide" id="manageLink"></a> |
				<a href="apps/help" id="helpModule">FAQ</a> | 
				<a href="mailto:dase@mail.laits.utexas.edu">email</a> | 
				<a href="copyright">Copyright/Usage Statement</a> | 
				<a href="resources">Resources</a> | 
				<a href="admin" class="hide" id="adminLink"></a> |
				{php}echo Dase_Timer::getElapsed();{/php} seconds |
				<img src="www/images/dasepowered.png" alt="DASePowered icon">
			</div><!--closes footer-->
			<div id="debugData" class="pagedata"></div>
			<div id="jsTemplates" class="pagedata"></div>

			{include file='resources/atom_template.tpl'}

		</body>
	</html>