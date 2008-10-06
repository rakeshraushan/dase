{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<h4 class="startSlideshow">
		<a href="#" id="startSlideshow">view as slideshow</a> |
		<a href="tag/{$items->eid}/{$items->asciiId}/sorter">slide sorter</a>
		{if $bulkedit}
		| <a href="tag/{$items->eid}/{$items->asciiId}/bulk editor">bulk editor</a>
		{/if}
	</h4>
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
	<div id="tagEid" class="pagedata">{$items->eid}</div>
	<div id="tagName" class="pagedata">{$items->name}</div>
	<div id="tagAsciiId" class="pagedata">{$items->asciiId}</div>
	<div id="tagType" class="pagedata">{$items->tagType}</div>
	<div class="spacer"></div>
</div>
{/block}
