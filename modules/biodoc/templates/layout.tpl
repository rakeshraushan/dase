{extends file="default.tpl"}

{block name="head"}
<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script> 
<script type="text/javascript" src="{$module_root}js/jquery.js"></script> 
<script type="text/javascript" src="{$module_root}js/autosuggest.js"></script> 
<script type="text/javascript" src="{$module_root}js/biodoc.js"></script> 
{block name="head-js"} {/block}
<link rel="stylesheet" type="text/css" href="{$module_root}css/biodoc.css">
<link rel="topics" type="text/css" href="{$app_root}attribute/biodoc/unit/values.json?limit=1000">
{block name="head-css"} {/block}
{/block}


{block name="body}
<div id="content">
	<div id="headBanner"><img src="{$module_root}images/ut.jpg"></div>
	<div id="logo"><img src="{$module_root}images/logo.jpg"></div>

	<div id="navigation">
		<a href="{$module_root}index" class="nav">Home</a> |
		<a href="{$module_root}about" class="nav">About</a> |
		<a href="{$module_root}contribute" class="nav">Contribute</a> |
		<a href="{$module_root}plugin" class="nav">Plug-ins</a> | 
		<a href="{$module_root}contact" class="nav">Contact</a>
	</div>

	<div id="sidebar">
		<div id="browseForm">
			<form id="unitForm" action="{$module_root}search" method="get">
				<p>Browse:</p>
				<select id="unitFormSelect" name="unit">
				</select>

				<div id="retrieveTopics" class="hide">retrieving topics...</div>

				<select id="topicSelect" class="hide" name="topic">
				</select>

				<input type="submit">
			</form>
		</div>

		<div id="searchForm">
			<form id="keywordForm" action="search" method="get">
				<p>Search:</p>

				<input size="12" name="txtSearch" type="text">
				<br>
				ex. "meiosis" or "DNA" 
				<p></p>
				<p>    
				<input type="submit">
				</p>
			</form>
		</div>
	</div>

	<div id="maincontent">
		{block name="main"}testing main{/block}
	</div>
</div>

{block name="footer"}{/block}
{/block}
