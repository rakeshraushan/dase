{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<div id="contentHeader">
		<h3>{$items->searchEcho}</h3>
		<!-- SEARCH FORM -->
		<form id="searchRefine" method="get" action="search">
			<div>
				<input id="queryInput" type="text" name="q" size="30"/>
				<input type="hidden" name="original_search" value="{$items->searchLink|replace:'search?':''}"/>
				<input type="submit" value="Refine Search" class="button"/>
			</div>
			<div id="refinements"></div>
		</form>
		<h4>
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
	</div> <!--close contentHeader -->
	<form id="saveToForm" method="post" action="save">	
		<table id="itemSet">
			{assign var=startIndex value=$items->startIndex}
			{include file='item_set/common.tpl' start=$startIndex}
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<div id="saveChecked"></div>
	</form>
	<div class="spacer"></div>
</div>
<div class="full" id="searchTallies">
	<h3>Search Results by Collection</h3>
	{$items->searchTallies}
</div>
<!-- we just need a place to stash the current url so our refine code can parse it -->
<div id="self_url" class="pagedata">{$items->searchLink|replace:'+':' '}</div>
{/block}
