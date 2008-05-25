{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<div id="contentHeader">
		<!-- SEARCH FORM -->
		<form id="searchCollectionsDynamic" method="get" action="search">
			<div>
				<input id="queryInput" type="text" name="q" size="30"/>
				<input type="submit" value="Search" class="button"/>
				<select id="collectionsSelect" name="collection_ascii_id">
				</select>
				<span id="preposition" class="hide">in</span>
				<select id="attributesSelect" class="hide">
				</select>
				<input id="refineCheckbox" type="checkbox"/>refine current result
			</div>
			<div id="refinements"></div>
		</form>
		<h3>{$items->searchEcho}</h3>
		<h4>
			<a href="{$items->previous}">prev</a> |
			<a href="{$items->next}">next</a> 
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
<div id="self_url" class="pagedata">{$items->self|replace:'+':' '}</div>
{/block}
