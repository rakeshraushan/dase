<tr>
	{* demonstration of filtering & sorting:*}
	{*assign var=items value=$items|filter:'culture':'Greek'|sortby:'title'*}

	{foreach key=j item=it from=$items->entries}
	{assign var=i value=$j+1}
	<td>
		<div class="checkNum">
			<input type="checkbox" name="item_unique[]" value="{$it->unique}"/>
			<span class="position">{$it->position}.</span>
		</div>
		<div class="cartAdd">
			<span class="hide">in cart</span> <a href="{$it->unique}" class="hide" id="addToCart_{$it->unique}">add to cart</a>
		</div>
		<div class="image">
			<a href="{$it->itemLink}">
				<img alt="image" src="{$it->thumbnailLink}"/>
			</a>
		</div>
		<div class="spacer"></div>
		<h5>
			{$it->_title|truncate:80:"..."}
		</h5>
		{if "" != $sort}
		{assign var=sort_attribute_value value=$it->$sort.text}
		<h5 class="sorted_by">{$it|label:$sort}: {$sort_attribute_value}</h5>
		{/if}
		<h5 class="collection_name">{$it->collection}</h5>
	</td>
	{if $i is div by 5}
	</tr><tr>
	{/if}
	{/foreach}
	<td class="blank" colspan="0">&nbsp;</td>
</tr>
