{extends file="layout.tpl"}
{block name="title"}DASe: Public User Sets{/block} 

{block name="head"}
{/block}

{block name="content"}
<div class="list" id="setlist">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Public User Sets</h1>
	<ul>
		{foreach item=set from=$sets->entries}
		<li>
		<a href="{$set->alternateLink}">{$set->title|escape} ({$set->itemCount} items)</a>
		</li>
		{/foreach}
	</ul>
</div>
{/block}
