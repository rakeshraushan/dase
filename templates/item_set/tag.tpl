{extends file="layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/item_set_display.js"></script>
<script type="text/javascript" src="www/scripts/dase/slideshow.js"></script>
{/block}

{block name="head-meta"}
<meta name="set_owner" contents="{$items->authorName}">
{/block}

{block name="head-links"}
<link rel="set_item_status" href="tag/{$items->authorName}/{$items->asciiId}/item_status">
{/block}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="{$items->tagType|lower|default:'set'}">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	{if $items->count}
	<div class="pageControls">
		<h4>

			{if 1 == $is_admin}
			<a href="tag/{$items->eid}/{$items->asciiId}/annotate">add slide captions</a> |
			<a href="tag/{$items->eid}/{$items->asciiId}/sorter">sort slides</a>
			{/if}
			{if $bulkedit}
			| <a href="collection/{$items->collectionAsciiId}/attributes" id="bulkEditor">bulk editor</a>
			{/if}
		</h4>
	</div>
	{/if}
	<h2>{$items->title} (<span {if 'cart' == $items->tagType}id="cartCount"{/if}>{$items->count}</span> items) 
		<span id="displaySelect"> [ 
			<a href="{$items->gridLink}">grid</a> | 
			<a href="{$items->listLink}">list</a> | 
			<a href="#" id="startSlideshow">slideshow</a> 
			<!-- | <a href="{$items->dataLink}">data</a>-->
			| <a href="tag/{$items->eid}/{$items->asciiId}/download">download</a>
			]<span></h2>
			<h3>{$items->subtitle}</h3>
			{if $items->count}
			<div id="ajaxFormHolder" class="hide">
				<!-- js templates retrieved asynchronously -->
			</div>
			<form id="saveToForm" method="post" action="save">	
				<table id="itemSet">
					{assign var=startIndex value=$items->startIndex}
					{if 'list' == $display}
					{include file='item_set/common_list.tpl' start=$startIndex}
					{else}
					{include file='item_set/common_grid.tpl' start=$startIndex}
					{/if}
				</table>
				<p>
				<a href="" id="checkall">check/uncheck all</a>
				</p>
				<div class="widget">
					<div id="saveChecked" {if 0 == $items->count}class="hide"{/if}></div>
				</div>
			</form>
			{if $is_admin}
			{if 'cart' == $items->tagType}
			<div class="widget">
				<form method="post" id="cartEmptyForm" action="user/{$items->eid}/cart/emptier">
					<input type="hidden" name="submit_confirm" value="are you sure you want to empty your cart?">
					<input type="submit" id="cartEmptyButton" value="empty cart">
				</form>
			</div>
			{else}
			<div class="widget">
				<form method="get" id="removeFromForm" action="{$items->link}">
					<p>
					<input type="submit" name="remove_checked" id="removeFromSet" value="remove checked items">
					</p>
				</form>
			</div>
			<div class="widget">
				<form method="post" id="setExpungerForm" action="tag/{$items->eid}/{$items->asciiId}/expunger">
					<input type="hidden" name="submit_confirm" value="are you sure?">
					<input type="submit" id="setExpungerButton" value="delete set">
				</form>
			</div>
			{/if}
			{/if}
			{else} {* number of items is zero *}
			{if $is_admin}
			{if 'cart' != $items->tagType}
			<div class="widget">
				<form method="post" id="setExpungerForm" action="tag/{$items->eid}/{$items->asciiId}/expunger">
					<input type="hidden" name="submit_confirm" value="are you sure?">
					<input type="submit" id="setExpungerButton" value="delete set">
				</form>
			</div>
			{/if}
			{/if}
			{/if}
			<div id="tagEid" class="pagedata">{$items->eid}</div>
			<div id="tagName" class="pagedata">{$items->name}</div>
			<div id="tagAsciiId" class="pagedata">{$items->asciiId}</div>
			<div id="tagType" class="pagedata">{$items->tagType}</div>
			{if $bulkedit}
			<!-- the presence of the following means editing is authorized -->
			<div id="collectionAsciiId" class="pagedata">{$items->collectionAsciiId}</div>
			{/if}
			<div class="spacer"></div>
		</div>
		{if 1 == $is_admin}
		<div class="{$items->tagType|lower|default:'set'}Admin">
			<form action="tag/{$items->eid}/{$items->asciiId}/visibility" method="post">
				<p>
				{if 1 == $is_public}
				<h5>This set is PUBLIC</h5>
				<input type="hidden" name="visibility" value="private">
				<input type="submit" value="make private">
				{else}
				<h5>This set is PRIVATE</h5>
				<input type="hidden" name="visibility" value="public">
				<input type="submit" value="make public">
				{/if}
				</p>
			</form>
		</div>
		{/if}
		{/block}
