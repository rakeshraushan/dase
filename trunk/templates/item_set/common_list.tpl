{* demonstration of filtering & sorting:*}
{*assign var=items value=$items|filter:'culture':'Greek'|sortby:'title'*}

{foreach key=j item=it from=$items->entries}
<tr class="item">
	<td class="thumb">
		<div class="checkNum">
			<input type="checkbox" name="item_unique[]" value="{$it->unique}"/>
			<span class="position">{$it->position}.</span>
		</div>
		<div class="image">
			<a href="{$it->itemLink}">
				<img alt="image" id="thumb{$it->serial_number}" src="{$it->thumbnailLink}"/>
			</a>
			<!--
			<a href="{$it->viewitemLink}" class="zoomer" id="zoom{$it->serial_number}">[+]</a>
			-->
		</div>
		<div class="spacer"></div>
		{if "" != $sort}
		{assign var=sort_attribute_value value=$it->$sort.text}
		<h5 class="sorted_by">{$it|label:$sort}: {$sort_attribute_value}</h5>
		{/if}
		<h5 class="collection_name">[{$it->collection}]</h5>
		{if $it->summary}
		<p class="thumbAnnotation">{$it->summary}</p>
		{/if}

	</td>
	<td class="metadata">
		<div class="cartAdd">
			<span class="hide">in cart</span> <a href="{$it->unique}" class="hide" id="addToCart_{$it->unique}">add to cart</a>
		</div>
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
		<!--
		<dl id="metadata" class="{$it->collectionAsciiId}">
			{foreach item=set key=ascii_id from=$it->metadata}
			<dt>{$set.attribute_name}</dt>
			{foreach item=value from=$set.values}
			<dd><a href="search?{$it->collectionAsciiId}.{$ascii_id}={$value.text|escape:'url'}">{$value.text}</a></dd>
			{/foreach}
			{/foreach}
		</dl>
		-->
	</td>
</tr>
{/foreach}
