{extends file="layout.tpl"}
{block name="title"}DASe: Public User Sets{/block} 

{block name="head"}
{/block}

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<ul>
		{foreach item=set from=$sets->entries}
		<li>
		<a href="{$set->alternateLink}">{$set->title|escape}</a>
		</li>
		{/foreach}
	</ul>
</div>
{/block}
