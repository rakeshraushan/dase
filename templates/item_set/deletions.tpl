{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="head"}
<script type="text/javascript" src="www/js/dase/item_set_display.js"></script>
{/block}

{block name="content"}
<div class="full" id="{$items->tagType|lower|default:'set'}">
	<div class="pageControls">
	</div>
	<h1>{$items->title}</h1>
	<table id="itemSet">
		{foreach key=j item=it from=$items->entries}
		<tr class="item">
			<td class="thumb">
				<div class="image">
					<a href="{$it->itemLink}"><img alt="image" id="thumb{$it->serial_number}" src="{$it->thumbnailLink}"/></a>
				</div>
				<div class="spacer"></div>
				<h5 class="collection_name">[{$it->collection}]</h5>
				{if $it->summary}
				<p class="thumbAnnotation">{$it->summary}</p>
				{/if}

			</td>
			<td class="metadata">
				<table id="metadata" class="{$it->collectionAsciiId}">
					{foreach item=set key=ascii_id from=$it->metadata}
					<tr>
						<th>{$set.attribute_name}</th>
						<td>
							<ul>
								{foreach item=value from=$set.values}
								<li>
								<a href="search?c={$it->collectionAsciiId}&amp;q={$ascii_id}:&quot;{$value.text|escape:'url'}&quot;">{$value.text} {if $value.mod}({$value.mod}){/if}</a>
								</li>
								{/foreach}
							</ul>
						</td>
						{/foreach}
					</tr>
				</table>
			</td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}
