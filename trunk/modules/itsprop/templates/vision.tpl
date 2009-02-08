{extends file="layout.tpl"}

{block name="head-links"}
<link rel="dept_props" href="{$props_link}" />
{/block}

{block name="head"}
<script type="text/javascript" src="scripts/dept_props.js"></script>
{/block}

{block name="content"}
<div class="main">
	<h1>Proposals for {$dept->dept_name.text}</h1>
	<h4>Chairperson: {$dept->dept_chair.text}</h4>
	<div id="vision_statement">{$dept->content}</div>
	<form method="post" action="{$dept->editContentLink}" class="hide" id="vision_form">
		{assign var=rows value=$dept->content|count_words}
		<textarea rows="{$rows/11}" id="vision_text" name="vision">{$dept->content}</textarea>
		<p>
		<input type="submit" value="update">
		</p>
	</form>
	<p><a href="ss" id="toggle_vision">add/edit vision statement</a></p>
	<h2 id="rankingProps" class="hide"><img src="{$app_root}www/images/indicator.gif"> updating proposal ranking...</h2>
	<div id="propsList">
		<h2 id="loadingProps"><img src="{$app_root}www/images/indicator.gif"> loading proposals...</h2>
	</div>
</div>
{/block}

