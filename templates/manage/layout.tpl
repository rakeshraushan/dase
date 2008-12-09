<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>
			{block name="title"}DASe: Digital Archive Services{/block}
		</title>
		<meta name="description" content="
		The Digital Archive Services project 
		is a lightweight digital content repository
		created by the College of Liberal Arts at 
		The University of Texas at Austin."/>

		<base href="{$app_root}"/>

		<link rel="stylesheet" type="text/css" href="www/css/yui.css"/>
		<link rel="stylesheet" type="text/css" href="www/css/style.css"/>
		<link rel="stylesheet" type="text/css" href="www/css/manage.css"/>
		<link rel="shortcut icon" href="www/images/favicon.ico"/>

		<script type="text/javascript" src="www/scripts/Base64.js"></script>
		<script type="text/javascript" src="www/scripts/http.js"></script>
		<script type="text/javascript" src="www/scripts/json2.js"></script>
		<script type="text/javascript" src="www/scripts/trimpath/template.js"></script>
		<script type="text/javascript" src="www/scripts/dase.js"></script>
		{block name="head"}{/block}


		<style type="text/css">
			ul#menu li.{$request->tab}-tab {literal}{{/literal}
				background-color: #eef1f8;
				background-color: #bbccdd;
				background-color: #496eac;
			{literal}}{/literal}
			ul#menu li.{$request->tab}-tab a {literal}{{/literal}
				color: #fff;
			{literal}}{/literal}
		</style>

		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="css/ie.css"/>
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
			<p id="collection_name">{$collection->collection_name}</p>
			<div class="hide" id="collection_ascii_id">{$collection->ascii_id}</div>
		</div>

		<div id="sidebar">
			<ul id="menu" class="hide">
				<li>
				<a href="collection/{$collection->ascii_id}">
					<img alt="icon" src="www/images/tango-icons/go-home.png"/><sup>Return to {$collection->collection_name}</sup>
				</a>
				</li>
				<li class="settings-tab">
				<a href="manage/{$collection->ascii_id}/settings">
					<img alt="icon" src="www/images/tango-icons/emblem-system.png"/><sup>Collection Settings</sup>
				</a>
				</li>
				<li class="attributes-tab">
				<a href="manage/{$collection->ascii_id}/attributes">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>Attributes</sup>
				</a>
				</li>
				<li class="item_types-tab">
				<a href="manage/{$collection->ascii_id}/item_types">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>Item Types</sup>
				</a>
				</li>
				<li class="managers-tab">
				<a href="manage/{$collection->ascii_id}/managers">
					<img alt="icon" src="www/images/tango-icons/contact-new.png"/><sup>Users/Managers</sup>
				</a>
				</li>
				<li class="uploader-tab">
				<a href="manage/{$collection->ascii_id}/uploader">
					<img alt="icon" src="www/images/tango-icons/list-add.png"/><sup>Create Item</sup>
				</a>
				</li>
				{if $module_menu}
				{include file="$module_menu"}
				{/if}
			</ul>
			<ul id="menuGrayed">
				<li>
				<a href="collection/{$collection->ascii_id}">
					<img alt="icon" src="www/images/tango-icons/go-home.png"/><sup>Return to {$collection->collection_name}</sup>
				</a>
				</li>
				<li class="settings-tab">
				<a href="manage/{$collection->ascii_id}/settings">
					<img alt="icon" src="www/images/tango-icons/emblem-system.png"/><sup>Collection Settings</sup>
				</a>
				</li>
				<li class="attributes-tab">
				<a href="manage/{$collection->ascii_id}/attributes">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>Attributes</sup>
				</a>
				</li>
				<li class="item_types-tab">
				<a href="manage/{$collection->ascii_id}/item_types">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>Item Types</sup>
				</a>
				</li>
				<li class="managers-tab">
				<a href="manage/{$collection->ascii_id}/managers">
					<img alt="icon" src="www/images/tango-icons/contact-new.png"/><sup>Users/Managers</sup>
				</a>
				</li>
				<li class="uploader-tab">
				<a href="manage/{$collection->ascii_id}/uploader">
					<img alt="icon" src="www/images/tango-icons/list-add.png"/><sup>Create Item</sup>
				</a>
				</li>
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
			<a href="admin" class="hide" id="adminLink"></a> |
			<a href="apps/help" id="helpModule">FAQ</a> | 
			<a href="mailto:dase@mail.laits.utexas.edu">email</a> | 
			<a href="copyright">Copyright/Usage Statement</a> | 
			<a href="resources">Resources</a> | 
			<a href="manage" class="hide" id="manageLink"></a> |
			<!--
			{$timer} seconds |
			-->
			{php}echo Dase_Timer::getElapsed();{/php} seconds |
			<img src="www/images/dasepowered.png" alt="DASePowered icon"/>
		</div><!--closes footer-->
		<div id="collectionAsciiId" class="pagedata">{$collection->ascii_id}</div>
		<div id="debugData" class="pagedata"></div>
	</body>
</html>
