{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<h2>{$items->title}</h2>
	<h3>{$items->subtitle}</h3>
	<form id="saveToForm" method="post" action="save">	
		<table id="itemSet">
			{assign var=startIndex value=$items->startIndex}
			{include file='item_set/common.tpl' start=$startIndex}
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<div id="saveChecked"></div>
	</form>
	<form method="get" id="removeFromForm" action="{$items->tagLink}">
		<input type="submit" name="remove_checked" id="removeFromSet" value="remove checked items from set"/>
	</form>
	<div id="tagName" class="pagedata">{$items->tagName}</div>
	<div id="tagAsciiId" class="pagedata">{$items->tagAsciiId}</div>
	<div class="spacer"></div>
</div>
{/block}
