{extends file="layout.tpl"}
{block name="title"}DASe: Collection Media List{/block} 

{block name="content"}
<div class="list" id="browse">
	<h1>{$collection->collection_name} Media List</h1>
	<ul>
		{foreach key=link item=title from=$media_links}
		<li>
		<a href="{$link}">{$title}</a>
		</li>
		{/foreach}
	</ul>
</div>
{/block}
