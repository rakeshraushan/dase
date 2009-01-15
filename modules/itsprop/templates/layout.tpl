<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>LAITS Technology Grants</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<base href="{$module_root}" />

		<link rel="stylesheet" type="text/css" href="{$app_root}www/css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/itsprop.css" />
		<link rel="shortcut icon" href="images/itsbox.ico" />


		<script type="text/javascript" src="{$app_root}www/scripts/webtoolkit.base64.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/http.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/json2.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/md5.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase/form.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/trimpath/template.js"></script>
		<script type="text/javascript" src="scripts/itsprop.js"></script>
		{block name="head"}{/block}

		<style type="text/css"></style>
	</head>

	<body>
		<div id="skipnav"><a href="#maincontent" title="Skip to main content">Skip to main content</a></div>

		<noscript>
			<h1 class="alert">This site requires Javascript!</h1>
		</noscript>

		<div id="container">
			<div id="masthead">
				<div class="itspropBanner">
					<div class="controls">

					</div>
					<h1>Liberal Arts Instructional Technology Services</h1>
					<h2>Technology Grants</h2>
				</div>
			</div>

			<div id="sidebar">

				<ul id="menu">
					<li class="home">
					<a href="u/{$user-eid}/home" class="main">Home</a>
					</li>
					<li>
					<a href="u/{$user-eid}" class="main">User Information</a>
					</li>
					<li>
					<a href="u/{$user-eid}/proposal/form" class="main">Create a Proposal</a>
					</li>
					<!-- user proposals here -->
					<li>
					<a href="admin/{$user-eid}/users" class="main">Manage Users</a>
					</li>
					<li>
					<a href="admin/{$user-eid}/departments" class="main">Manage Departments</a>
					</li>
					<li>
					<a href="admin/{$user-eid}/proposals" class="main">Proposals List</a>
					</li>
				</ul>
				<div class="loadingMsg" id="ajaxMsg"></div>
			</div> 

			<div id="content">
				<div id="itsprop" class="full">
					{block name="content"}default content{/block}
				</div>
			</div>

			<div class="spacer"></div>
			<hr />
			<div id="footer">
				<img src="images/its.gif" title="LAITS" class="logo" alt="LAITS" height="33" width="79" /><a href="http://www.laits.utexas.edu/its/">Liberal Arts ITS</a>
				| <a href="mailto:www@mail.laits.utexas.edu">www@mail.laits.utexas.edu</a> 
				| <a href="http://daseproject.org"><img height="11" id="daseLogo" width="71" alt="DASe powered icon" title="DASe powered!" src="images/dasepowered.png" /></a>
				| <span id="date"></span>
			</div>
			<div id="debugData" class="pagedata"></div>
			{include file='atom_template.tpl'}
		</div>
	</body>
</html>
