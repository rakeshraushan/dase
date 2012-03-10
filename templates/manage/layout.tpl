<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>
			{block name="title"}DASe: Digital Archive Services{/block}
		</title>
		<meta name="description" content="
		The Digital Archive Services project 
		is a lightweight digital content repository
		created by the College of Liberal Arts at 
		The University of Texas at Austin.">

		<base href="{$app_root}">

		<link rel="stylesheet" type="text/css" href="www/css/yui.css">
		<link rel="stylesheet" type="text/css" href="www/css/style.css">
		<link rel="stylesheet" type="text/css" href="www/css/manage.css">
		{block name="head-links"}{/block}
		<link rel="shortcut icon" href="www/images/favicon.ico">

		<script type="text/javascript" src="www/js/webtoolkit.base64.js"></script>
		<script type="text/javascript" src="www/js/http.js"></script>
		<script type="text/javascript" src="www/js/json2.js"></script>
		<script type="text/javascript" src="www/js/dase.js"></script>
		<script type="text/javascript" src="www/js/dase/htmlbuilder.js"></script>
		<script type="text/javascript" src="www/js/dase/atompub.js"></script>
		{block name="head"}{/block}


		<style type="text/css">
			ul#menu li.{$request->tab}-tab {literal}{{/literal}
				background-color: #7da2e0;
			{literal}}{/literal}
			ul#menu li.{$request->tab}-tab a {literal}{{/literal}
				color: #fff;
			{literal}}{/literal}
		</style>

		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="css/ie.css">
		<![endif]-->

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
			<div id="sort" class="pagedata">{$sort}</div>
		</div>

		<div id="loginControl" class="login hide">
			<div>Got a UT EID? <a href="login/form" class="alert">login!</a></div>
		</div>

		<div id="adminHeader">
			DASe Collection Builder
			<p id="collection_name"><a href="collection/{$collection->ascii_id}">{$collection->collection_name}</a></p>
			<div class="hide" id="collection_ascii_id">{$collection->ascii_id}</div>
		</div>

		<div id="sidebar">
			<ul id="menu" class="hide">
				<li>
				<a href=".">
					<img alt="icon" src="www/images/tango-icons/go-home.png"><sup>Home/Search</sup>
				</a>
				</li>
				<li class="settings-tab">
				<a href="manage/{$collection->ascii_id}/settings">
					<img alt="icon" src="www/images/tango-icons/emblem-system.png"><sup>Collection Settings</sup>
				</a>
				</li>
				<li class="attributes-tab">
				<a href="manage/{$collection->ascii_id}/attributes">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"><sup>Attributes</sup>
				</a>
				</li>
				<li class="item_types-tab">
				<a href="manage/{$collection->ascii_id}/item_types">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"><sup>Item Types</sup>
				</a>
				</li>
				<li class="delete_items-tab">
				<a href="manage/{$collection->ascii_id}/delete_items">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"><sup>Delete Items</sup>
				</a>
				</li>
				<li class="managers-tab">
				<a href="manage/{$collection->ascii_id}/managers">
					<img alt="icon" src="www/images/tango-icons/contact-new.png"><sup>Users/Managers</sup>
				</a>
				</li>
				<li class="uploader-tab">
				<a href="manage/{$collection->ascii_id}/uploader">
					<img alt="icon" src="www/images/tango-icons/list-add.png"><sup>Create Item(s)</sup>
				</a>
				</li>
				{if $module_menu}
				{include file="$module_menu"}
				{/if}
			</ul>
			<ul id="menuGrayed">
				<li>
				<a href="collection/{$collection->ascii_id}">
					<img alt="icon" src="www/images/tango-icons/go-home.png"><sup>Home/Search</sup>
				</a>
				</li>
				<li class="settings-tab">
				<a href="manage/{$collection->ascii_id}/settings">
					<img alt="icon" src="www/images/tango-icons/emblem-system.png"><sup>Collection Settings</sup>
				</a>
				</li>
				<li class="attributes-tab">
				<a href="manage/{$collection->ascii_id}/attributes">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"><sup>Attributes</sup>
				</a>
				</li>
				<li class="item_types-tab">
				<a href="manage/{$collection->ascii_id}/item_types">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"><sup>Item Types</sup>
				</a>
				<li class="delete_items-tab">
				<a href="manage/{$collection->ascii_id}/delete_items">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"><sup>Delete Items</sup>
				</a>
				</li>
				<li class="managers-tab">
				<a href="manage/{$collection->ascii_id}/managers">
					<img alt="icon" src="www/images/tango-icons/contact-new.png"><sup>Users/Managers</sup>
				</a>
				</li>
				<li class="uploader-tab">
				<a href="manage/{$collection->ascii_id}/uploader">
					<img alt="icon" src="www/images/tango-icons/list-add.png"><sup>Create Item</sup>
				</a>
				</li>
				{if $module_menu}
				{include file="$module_menu"}
				{/if}
			</ul>
			<h5 id="ajaxMsg" class="hide">loading...</h5>
		</div> <!-- closes sidebar -->

		<div id="content">
			<div id="admin" class="full">
				{block name="content"}default content{/block}
			</div>
		</div>

		<div class="spacer"></div>

		<div id="footer">
			<a href="manage" class="hide" id="manageLink"></a> |
			<a href="apps/help" id="helpModule">FAQ</a> | 
			<a href="mailto:dase@mail.laits.utexas.edu">email</a> | 
			<a href="copyright">Copyright/Usage Statement</a> | 
			<a href="resources">Resources</a> | 
			<a href="admin" class="hide" id="adminLink"></a> |
			{$request->elapsed} seconds |
			<img src="www/images/dasepowered.png" alt="DASePowered icon">
		</div><!--closes footer-->
		<div id="collectionAsciiId" class="pagedata">{$collection->ascii_id}</div>
		<div id="debugData" class="pagedata"></div>
	</body>
</html>
