{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/search_sorting.js"></script>
<script type="text/javascript" src="www/scripts/dase/item_set_display.js"></script>
<script type="text/javascript" src="www/scripts/dase/slideshow.js"></script>
{/block}

{block name="content"}
<div class="full" id="results">
	<div id="msg" class="alert hide"></div>
	{if $items->count}
	<div class="pageControls">
		<!-- for sorting results -->
		<div id="sortByAttFormDiv"></div>
	</div>
	{/if}


	<!-- javascript template for sort by attribute pull-down-->
	<textarea class="javascript_template" id="attributes_jst">
		{literal}
		<form id="sortByAttForm" action="search" method="get">
			<select name="att_ascii_id">
				<option value="">select an attribute</option>
				{for att in atts}
				<option value="${att.ascii_id}">${att.attribute_name}</option>
				{/for}
			</select>
			<input type='submit' value='sort results'/>
		</form>
		{/literal}
	</textarea>
	<!-- end javascript template -->

	<div id="contentHeader">
		{if $items->collection}
		<h2 class="collectionLink"><a href="{$items->collection.href}">{$items->collection.title}</a></h2>
		{/if}
		{if $items->count}
		<h3 class="searchEcho">Search Results {$start} - {$end} of {$items->count}  
			<span id="displaySelect">[ 
				<a href="{$items->gridLink}">grid</a> | 
				<a href="{$items->listLink}">list</a> | 
				<a href="#" id="startSlideshow">slideshow</a> 
				{if $items->collection}
				| <a href="{$items->dataLink}">data</a> 
				{/if}
				]<span></h3>
		{else}
		<h3 class="searchEcho">Search Results: 0 items found</h3>
		{/if}
		<!-- SEARCH FORM -->
		<form id="searchRefine" method="get" action="search">
			<div>
				<input id="queryInput" type="text" name="q" size="40" value="{$items->query}"/>
				<!--
				<input type="hidden" name="original_search" value="{$items->searchLink|replace:'search?':''}"/>
				-->
				<input type="submit" value="Search" class="button"/>
			</div>
			<div id="refinements"></div>
		</form>
		{if $items->count > $items->max}
		<h4 class="pagerControl">
			{if $items->previous}
			<a href="{$items->previous}">prev</a> 
			{else}
			<span class="nolink">prev</span>
			{/if}
			|
			{if $items->next}
			<a href="{$items->next}">next</a> 
			{else}
			<span class="nolink">next</span>
			{/if}
		</h4>
		{/if}
	</div> <!--close contentHeader -->
	<form id="saveToForm" method="post" action="save">	
		<table id="itemSet">
			{assign var=startIndex value=$items->startIndex}
			{if 'list' == $display}
			{include file='item_set/common_list.tpl' start=$startIndex}
			{else}
			{include file='item_set/common_grid.tpl' start=$startIndex}
			{/if}
		</table>
		{if $items->count > $items->max}
		<h4 class="pagerControl">
			{if $items->previous}
			<a href="{$items->previous}">prev</a> 
			{else}
			<span class="nolink">prev</span>
			{/if}
			|
			{if $items->next}
			<a href="{$items->next}">next</a> 
			{else}
			<span class="nolink">next</span>
			{/if}
		</h4>
		{/if}
		{if $items->count}
		<a href="" id="checkall">check/uncheck all</a>
		<div>&nbsp;</div>
		<div class="widget">
			<div id="saveChecked"></div>
		</div>
		{/if}
	</form>
	<div class="spacer"></div>
</div>
{if $items->count}
<div class="full" id="searchTallies">
	<h3>Search Results by Collection</h3>
	{$items->searchTallies}
</div>
{/if}
<!-- we just need a place to stash the current url so our refine code can parse it -->
<div id="self_url" class="pagedata">{$items->searchLink|replace:'+':' '}</div>
<div id="attributes_json_url" class="pagedata">{$items->attributesLink}</div>
{/block}
