<tr>
	{* demonstration of filtering & sorting:*}
	{*assign var=items value=$items|filter:'culture':'Greek'|sortby:'title'*}

	{foreach key=j item=it from=$items->entries}
	{assign var=i value=$j+1}
	<td>
		<div class="checkNum">
			<input type="checkbox" name="item_id[]" value="{$it->itemId}"/>
		</div>
		<div class="cartAdd">
			<span class="hide">in cart</span> <a href="#" class="hide" id="addToCart_{$it->itemId}">add to cart</a>
		</div>
		<div class="image">
			<a href="{$it->itemLink}">
				<img alt="" src="{$it->thumbnailLink}"/>
			</a>
		</div>
		<div class="spacer"></div>
		<h5>
			{$it->title}
		</h5>
		<h5 class="collection_name">{$it->collection}</h5>
	</td>
	{if $i is div by 5}
	</tr><tr>
	{/if}
	{/foreach}
	<td class="blank" colspan="0">&nbsp;</td>
</tr>
