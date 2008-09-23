{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/search_sorting.js"></script>
{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	{if $items->count}
	<div class="pageControls">
		<h4 class="startSlideshow">
			<a href="#" id="startSlideshow">view as slideshow</a>
		</h4>
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
		<h3><a href="{$items->collection.href}">{$items->collection.title}</a></h3>
		<!-- SEARCH FORM -->
		<form id="searchRefine" method="get" action="search">
			<div>
				<input id="queryInput" type="text" name="q" size="60" value="{$items->query}"/>
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
			{include file='item_set/common.tpl' start=$startIndex}
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
		<div id="saveChecked"></div>
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
<div id="attributes_json_url" class="pagedata">{$items->attributesLink}<div>
{/block}
