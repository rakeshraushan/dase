{extends file="layout.tpl"}
{block name="title"}View Item{/block}
{block name="content"}
<div class="full" id="{$item->tagType}">
	<div id="collectionAsciiId" class="pagedata">{$item->collectionAsciiId}</div>
	<div id="contentHeader">
		<h1><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a></h1>
		<h2>{$item->title}</h2>
		<h3>{$item->subtitle}</h3>
		<h4>
			<a href="{$item->previous}">prev</a> |
			<a href="{$item->feedLink}">up</a> |
			<a href="{$item->next}">next</a> 
		</h4>
	</div> <!-- close contentHeader -->
	<table id="item">
		<tr>
			<td class="image">
				<img src="{$item->viewitemLink}"/>
				<h4>Media:</h4>
				<ul>
					{foreach item=img from=$item->media}
					<li><a href="{$img.href}">{$img.label}: {$img.width}x{$img.height} ({$img.type})</a></li>
					{/foreach}
				</ul>
			</td>
			<td class="metadata">
				<h3><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a></h3>
				<dl id="metadata" class="{$item->collectionAsciiId}">
					{foreach item=set key=ascii_id from=$item->metadata}
					<dt>{$set.attribute_name}</dt>
					{foreach item=value from=$set.values}
					<dd><a href="search?{$item->collectionAsciiId}.{$ascii_id}={$value|escape:'url'}">{$value}</a></dd>
					{/foreach}
					{/foreach}
				</dl>
				<ul id="itemLinks">
					<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}.atom">atom</a>
				</ul>
			</td>
		</tr>
	</table>
	{if $item->editLink}
	<div><a class="hide" id="editLink" href="{$item->editLink}">edit item</a></div>
	{/if}
</div> <!-- close content -->
{/block} 
