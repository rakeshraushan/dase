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
		<link rel="stylesheet" type="text/css" href="www/css/test.css"/>
		<link rel="shortcut icon" href="www/images/favicon.ico"/>

		<script type="text/javascript" src="www/js/webtoolkit.base64.js"></script>
		<script type="text/javascript" src="www/js/http.js"></script>
		<script type="text/javascript" src="www/js/json2.js"></script>
		<script type="text/javascript" src="www/js/md5.js"></script>
		<script type="text/javascript" src="www/js/dase.js"></script>
		<script type="text/javascript" src="www/js/dase/form.js"></script>
		<script type="text/javascript" src="www/js/dase/htmlbuilder.js"></script>
		{block name="head"}{/block}

		<style type="text/css">
			{block name="style"}{/block}
			ul#menu li.{$request->resource}-tab {literal}{{/literal}
				background-color: #7da2e0;
			{literal}}{/literal}
			ul#menu li.{$request->resource}-tab a {literal}{{/literal}
				color: #fff;
			{literal}}{/literal}
		</style>

		<!--[if lt IE 8]>
		<link rel="stylesheet" type="text/css" href="css/ie.css"/>
		<![endif]-->

	</head>
	<body>

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
			DASe Tools 
		</div>

		<div id="sidebar">
			<ul id="menu" class="hide">
				<li>
				<a href="collections">
					<img alt="icon" src="www/images/tango-icons/go-home.png"/><sup>Return to DASe</sup>
				</a>
				</li>
				<li class="demo-tab">
				<a href="tools/demo">
					<img alt="icon" src="www/images/tango-icons/utilities-terminal.png"/><sup>AtomPub Demo</sup>
				</a>
				</li>
				<li class="htmlbuilder-tab">
				<a href="tools/htmlbuilder">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>HTML Builder Demo</sup>
				</a>
				</li>
			</ul>
			<ul id="menuGrayed">
				<li>
				<a href="collections">
					<img alt="icon" src="www/images/tango-icons/go-home.png"/><sup>Return to DASe</sup>
				</a>
				</li>
				<li class="demo-tab">
				<a href="tools/demo">
					<img alt="icon" src="www/images/tango-icons/utilities-terminal.png"/><sup>AtomPub Demo</sup>
				</a>
				</li>
				<li class="htmlbuilder-tab">
				<a href="tools/htmlbuilder">
					<img alt="icon" src="www/images/tango-icons/preferences-system.png"/><sup>HTML Builder Demo</sup>
				</a>
				</li>
			</ul>
			<h5 id="ajaxMsg">loading...</h5>
		</div> <!-- closes sidebar -->

		<div id="content">
			<div id="tools" class="full">

				{block name="content"}default content{/block}
			</div>
		</div>

		<div class="spacer"></div>

		<div id="debugData" class="pagedata"></div>
	</body>
</html>
