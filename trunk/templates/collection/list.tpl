{extends file="layout.tpl"}
{block name="title"}DASe: Collections List{/block} 

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/collection_list.js"></script>
{/block}

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h5>Search selected collection(s):</h5> 
	<form id="homeSearchForm" method="get" action="search">
		<div class="searchForm">
			<input type="text" name="q" size="30" value="{$failed_query|urldecode|htmlspecialchars}"/>
			<input type="submit" value="Search" class="button"/>
		</div>
		<div>
			<a href="#" id="checkall">check/uncheck all</a>
		</div>
		<ul id="collectionList" class="multicheck">
			{foreach item=c from=$collections->entries}
			<li id="{$c->asciiId}">
			<input name="c" value="{$c->asciiId}" checked="checked" type="checkbox"/>
			<a href="collection/{$c->asciiId}" class="checkedCollection">{$c->name|escape}</a>
			<span class="tally">({$c->itemCount|default:0})</span>
			</li>
			{/foreach}
			<li id="specialAccessLabel" class="hide"><h4>Special Access Collections</h4></li>
		</ul>
	</form>
</div>
{/block}
