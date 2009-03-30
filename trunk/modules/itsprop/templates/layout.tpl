<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
	<head>
		<title>LAITS Technology Grants</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >

		<base href="{$module_root}" >

		<!--
		<link rel="stylesheet" type="text/css" href="{$app_root}www/css/style.css" >
		-->
		<link rel="stylesheet" type="text/css" href="css/itsprop.css" >
		<link rel="shortcut icon" href="images/itsbox.ico" >
		<link rel="proposals" href="{$app_root}item_type/itsprop/proposal/items/{$user->eid}.json" >
		<link rel="service_pass" href="{$module_root}service_pass/itsprop" >
		 {block name="head-links"}{/block}

		<script type="text/javascript" src="{$app_root}www/scripts/webtoolkit.base64.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/http.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/json2.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/md5.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase/form.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase/atompub.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase/htmlbuilder.js"></script>
		<script type="text/javascript" src="scripts/itsprop.js"></script>
		<script type="text/javascript" src="scripts/jquery-1.2.6.js"></script>
		<script type="text/javascript" src="scripts/jquery.tablesorter.js"></script>
		<script type="text/javascript" src="scripts/jquery-ui-personalized-1.5.3.js"></script>
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
					{if $user}
					<div class="controls">
						<a href="logout" class="logout">logout {$user->eid}</a>
						{if $request->is_evaluator}
						(evaluator)
						{/if}
					</div>
					{/if}
					<h3>Liberal Arts Instructional Technology Services</h3>
					<h1>Technology Grants</h1>
				</div>
			</div>

			<div id="sidebar">

				{if $user->eid}
				<ul id="menu">
					<!--
					<li class="home">
					<a href="home" class="main">Home</a>
					</li>
					<li>
					<a href="person/{$user->eid}" class="main">User Information</a>
					</li>
					<li>
					<a href="person/{$user->eid}/proposal_form" class="main">Create a Proposal</a>
					</li>
					-->
					<li class="headline hide" id="propsLabel">Your Proposals:</li>
					<li>
					<ul id="userProposals"></ul>
					</li>

					{if $request->is_evaluator}
					<li class="headline">Evaluator Links:</li>
					<li>
					<a href="proposals" class="main">Proposals List</a>
					</li>
					{/if}

					{if $request->is_chair}
					<li class="headline">Your Departments:</li>
					{/if}

					{foreach item=dept from=$request->chair_feed->entries}
					<li>
					<a href="department/{$dept->dept_id.text}/vision" class="main">{$dept->dept_name.text} Proposals</a>
					</li>
					{/foreach}

					{if $request->is_superuser}
					<li class="headline">Site Administration:</li>
					<li>
					<a href="persons" class="main">Manage Users</a>
					</li>
					<li>
					<a href="departments" class="main">Manage Departments</a>
					</li>
					<li>
					<a href="proposals" class="main">Proposals List</a>
					</li>
					{/if}

				</ul>
				<h5 class="hide" id="ajaxMsg">loading...</h5>
				{/if}
			</div> 

			<div id="content">
				<!--
				<h4 class="highlight">This website was recently upgraded.  Please report any problems to pkeane@mail.utexas.edu. <br>There remain unresolved problems with the IE browser please use Firefox or Safari.</h4>
				-->
				<h4 class="highlight">Thanks to all who submitted proposals. The submission period has now ended.</h4>
				{if $msg}<h3 class="msg">{$msg}</h3>{/if}
				{block name="content"}default content{/block}
			</div>

			<div class="spacer"></div>
			<hr >
			<div id="footer">
				<img src="images/its.gif" title="LAITS" class="logo" alt="LAITS" height="33" width="79" ><a href="http://www.laits.utexas.edu/its/">Liberal Arts ITS</a>
				| <a href="mailto:proposalhelp@mail.laits.utexas.edu">proposalhelp@mail.laits.utexas.edu</a> 
				| <a href="http://daseproject.org"><img height="11" id="daseLogo" width="71" alt="DASe powered icon" title="DASe powered!" src="images/dasepowered.png" ></a>
				| <span id="date"></span>
				| <span>{$timer} seconds</span>
			</div>
			<div id="debugData" class="pagedata"></div>
		</div>
	</body>
</html>
