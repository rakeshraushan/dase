<!doctype html>
<html lang="en">
	<head>
		<base href="{$app_root}">
		<meta charset="utf-8">
		{block name="head-meta"}{/block}

		<title>{block name="title"}{/block}</title>

		<link rel="stylesheet" href="www/css/base.css">
		<link rel="stylesheet" href="www/css/style.css">
		{block name="head-links"}{/block}

		{block name="head-js"}
		<!--
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="http://code.jquery.com/ui/1.8.19/jquery-ui.min.js"></script>
		-->
		<script src="www/js/jquery.js"></script>
		<script src="www/js/jquery-ui.js"></script>
		{/block}

		{block name="head"}{/block}
		<script src="www/js/script.js"></script>

	</head>
	<body>
		<div id="wordmark">{block name="wordmark"}{/block}</div>
		<div id="container">
			<div id="header">{block name="header"}{/block}</div>
			<div id="sidebar">{block name="sidebar"}{/block}</div>
			<div id="main">{block name="main"}{/block}</div>
			<div class="clear"></div>
			<div id="footer">{block name="footer"}{/block}</div>
		</div>
	</body>
</html>
