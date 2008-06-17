{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<h2>{$items->title} ({$items->count} items)</h2>
	<h3>{$items->subtitle}</h3>
	<form id="saveToForm" method="post" action="save">	
		<table id="itemSet">
			{assign var=startIndex value=$items->startIndex}
			{include file='item_set/common.tpl' start=$startIndex}
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<div id="saveChecked"></div>
	</form>
	<!-- why "get"?????? -->
	<form method="get" id="removeFromForm" action="{$items->tagLink}">
		<input type="submit" name="remove_checked" id="removeFromSet" value="remove checked items from set"/>
	</form>
	<!-- hijack to delete-->
	{if $items->count < 1} 
	<form method="post" id="deleteTag" action="{$items->tagLink}">
		<input type="submit" name="deleteTag" id="deleteTag" value="delete this set"/>
	</form>
	{/if}
	<div id="tagName" class="pagedata">{$items->tagName}</div>
	<div id="tagAsciiId" class="pagedata">{$items->tagAsciiId}</div>
	<div class="spacer"></div>
</div>
{/block}
