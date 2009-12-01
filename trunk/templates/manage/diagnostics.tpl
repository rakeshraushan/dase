{extends file="manage/layout.tpl"}

{block name="head"}
{/block}

{block name="title"}DASe: {$collection->collection_name|escape}{/block} 

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Diagnostics for {$collection->collection_name}</h1>
</div>
<div id="collectionData">
	<h3>{$unindexed_count} items need to be indexed (items modified since {$latest_adjusted})</h3>
	<form action="{$app_root}manage/{$collection->ascii_id}/index_update" method="post">
		<input type="submit" value="update search index">
	</form>
	<div class="spacer"></div>
</div>
{/block} 

