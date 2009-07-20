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
	<h2>latest search index timestamp</h2>
	<h3>{$latest}</h3>
	<div class="spacer"></div>
</div>
{/block} 

