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
		<link rel="stylesheet" type="text/css" href="www/css/admin.css"/>
		<link rel="shortcut icon" href="www/images/favicon.ico"/>

		<script type="text/javascript" src="www/scripts/http.js"></script>
		<script type="text/javascript" src="www/scripts/json2.js"></script>
		<script type="text/javascript" src="www/scripts/md5.js"></script>
		<script type="text/javascript" src="www/scripts/dase.js"></script>
		<script type="text/javascript" src="www/scripts/dase/form.js"></script>
		<script type="text/javascript" src="www/scripts/trimpath/template.js"></script>
		{block name="head"}{/block}

		<style type="text/css">
			{block name="style"}{/block}
			ul#menu li.{$request->resource}-tab {literal}{{/literal}
				background-color: #eef1f8;
				background-color: #bbccdd;
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
		</div>

		<div id="loginControl" class="login hide">
			<div>Got a UT EID? <a href="login/form" class="alert">login!</a></div>
		</div>

		<div id="manageHeader">
			DASe Archive Administration 
		</div>

		<div id="sidebar">
			<ul id="menu">
				<li>
				<a href="collections">
					<img alt="icon" src="www/images/tango-icons/go-home.png"/><sup>Return to DASe</sup>
				</a>
				</li>
				<li class="docs-tab">
				<a href="admin/docs">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>Class Documentation</sup>
				</a>
				</li>
				<li class="log-tab">
				<a href="admin/log">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>View Log</sup>
				</a>
				</li>
				<li class="attributes-tab">
				<a href="admin/attributes">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>Admin Attributes</sup>
				</a>
				</li>
				<li class="managers-tab">
				<a href="admin/users">
					<img alt="icon" src="www/images/tango-icons/contact-new.png"/><sup>Users/Managers</sup>
				</a>
				</li>
				<li class="collection-tab">
				<a href="admin/collection/form">
					<img alt="icon" src="www/images/tango-icons/list-add.png"/><sup>Create a New Collection</sup>
				</a>
				</li>
				<li class="remove-collection-tab">
				<a href="admin/collections">
					<img alt="icon" src="www/images/tango-icons/list-remove.png"/><sup>Delete a Collection</sup>
				</a>
				</li>
			</ul>
			<h5 id="ajaxMsg"></h5>
		</div> <!-- closes sidebar -->

		<div id="content">
			<div id="manage" class="full">
				{block name="content"}default content{/block}
			</div>
		</div>

		<div class="spacer"></div>

		<div id="debugData" class="pagedata"></div>
	</body>
</html>
