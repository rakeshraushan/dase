{extends file="layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/item_set_display.js"></script>
<script type="text/javascript" src="www/scripts/dase/slideshow.js"></script>
{/block}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="{$items->tagType|lower|default:'set'}">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	{if $items->count}
	<div class="pageControls">
		<h4>
			
			<a href="tag/{$items->eid}/{$items->asciiId}/sorter">slide sorter</a>
			{if $bulkedit}
			| <a href="tag/{$items->eid}/{$items->asciiId}/bulk editor" id="bulkEditor">bulk editor</a>
			{/if}
		</h4>
	</div>
	{/if}
	<h2>{$items->title} (<span {if 'cart' == $items->tagType}id="cartCount"{/if}>{$items->count}</span> items) <span id="displaySelect">[ <a href="{$items->gridLink}">grid</a> | <a href="{$items->listLink}">list</a> | <a href="#" id="startSlideshow">slideshow</a> ]<span></h2>
	<h3>{$items->subtitle}</h3>
	{if $items->count}
	<form id="saveToForm" method="post" action="save">	
		<table id="itemSet">
			{assign var=startIndex value=$items->startIndex}
			{if 'list' == $display}
			{include file='item_set/common_list.tpl' start=$startIndex}
			{else}
			{include file='item_set/common_grid.tpl' start=$startIndex}
			{/if}
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<div>&nbsp;</div>
		<div class="widget">
			<div id="saveChecked" {if 0 == $items->count}class="hide"{/if}></div>
		</div>
	</form>
	{if $is_admin}
	{if 'cart' == $items->tagType}
	<div class="widget">
		<form method="post" id="cartEmptyForm" action="user/{$items->eid}/cart/emptier">
			<input type="hidden" name="submit_confirm" value="are you sure you want to empty your cart?"/>
			<input type="submit" id="cartEmptyButton" value="empty cart"/>
		</form>
	</div>
	{else}
	<div class="widget">
		<form method="get" id="removeFromForm" action="{$items->link}">
			<input type="submit" name="remove_checked" id="removeFromSet" value="remove checked items"/>
		</form>
	</div>
	<div class="widget">
		<!-- why "get"?????? -->
		<form method="post" id="setExpungerForm" action="tag/{$items->eid}/{$items->asciiId}/expunger">
			<input type="hidden" name="submit_confirm" value="are you sure?"/>
			<input type="submit" id="setExpungerButton" value="delete entire set"/>
		</form>
	</div>
	{/if}
	{/if}
	{/if}
	<div id="tagEid" class="pagedata">{$items->eid}</div>
	<div id="tagName" class="pagedata">{$items->name}</div>
	{if $bulkedit}
	<div id="collectionAsciiId" class="pagedata">{$items->collectionAsciiId}</div>
	{/if}
	<div id="tagAsciiId" class="pagedata">{$items->asciiId}</div>
	<div id="tagType" class="pagedata">{$items->tagType}</div>
	<div class="spacer"></div>
</div>
{/block}
