{extends file="layout.tpl"}

{block name="content"}


<dl>
	{assign var=sorted_feed value=$feed|sortby:'title'}
	{foreach item=entry from=$sorted_feed->entries}
	<dt>{$entry->title.text}</dt>
	<dd>{$entry->description.text}</dd>
	{/foreach}
</dl>

{/block}



