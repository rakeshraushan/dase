<!DOCTYPE html>
<html lang="en">
	<head>
		<base href="{$app_root}">
		<meta charset="utf-8">
		{block name="head-meta"}{/block}


		<title>{block name="title"}{/block}</title>

		<!-- Le styles -->
		<link href="www/css/bootstrap.css" rel="stylesheet">
		<link href="www/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="www/css/local.css" rel="stylesheet">

		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

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

		{block name="header"}
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<a class="brand" href="#">Project name</a>
					<div class="nav-collapse">
						<ul class="nav">
							<li class="active"><a href="#">Home</a></li>
							<li><a href="#about">About</a></li>
							<li><a href="#contact">Contact</a></li>
						</ul>
					</div><!--/.nav-collapse -->
				</div>
			</div>
		</div>
		{/block}

		<div class="container">
			{block name="main"}
			{if $msg}<h3 class="msg">{$msg}</h3>{/if}
			{block name="content"}

			<!-- Main hero unit for a primary marketing message or call to action -->
			<div class="hero-unit">
				<h1>Testing Bootstrap</h1>
				<p>This is a template for a simple marketing or informational website. It includes a large callout called the hero unit and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
				<p><a class="btn btn-primary btn-large">Learn more &raquo;</a></p>
			</div>

			<!-- Example row of columns -->
			<div class="row">
				<div class="span4">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
				<div class="span4">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
				<div class="span4">
					<h2>Heading</h2>
					<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
			</div>

			<div class="row">
				<div class="span6">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
				<div class="span6">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
			</div>

			<hr>
			{/block}

			{block name="footer"}
			<footer>
			<p>&copy; Company 2012</p>
			</footer>
			{/block}

			{/block}
		</div> <!-- /container -->

	</body>
</html>
