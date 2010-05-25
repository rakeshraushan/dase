{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/js/dase/delete_items.js"></script>
{/block}

{block name="title"}DASe: {$collection->collection_name|escape}{/block} 

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Items Marked for Deletion from {$collection->collection_name} ({$doomed|@count} total)</h1>
</div>
<div id="collectionData">
	<form id="deleter">
	<ul class="multicheck" id="fileList">
		{foreach item=url from=$doomed}
		<li>
		<img src="{$app_root}www/images/indicator.gif" class="hide"/>
		<input type="checkbox" checked="checked" value="{$url}" name="file_to_delete"/>
		<a class="checked" href="{$url}">{$url}</a>
		</li>
		{/foreach}
	</ul>
	<p id="checker">
	<a href="#" id="checkall">check/uncheck all</a>
	</p>
	<input id="submitButton" type="submit" value="delete checked files"/>
</form>
<div class="spacer"></div>
</div>
{/block} 

