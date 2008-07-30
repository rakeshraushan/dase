{literal}
<textarea class="javascript_template" id="tag_jst">
	<tr id="sortables">
		{var i=0}
		{for ti in tag.items}
		<td id="cell_${i++}">
			<a href="${ti.url}/sorter" class="moveto ${i-5} ${i}"><img src="www/images/tango-icons/go-up.png"/></a> 
			<div class="checkNum">
				<input type="checkbox" name="item_id[]" value="${ti.item_unique}"/>
				<span class="numberInSet">${i}.</span>
			</div>

			<div class="cartAdd">
				<span class="hide">in cart</span> <a href="#" class="hide" id="addToCart_${ti.item_unique}">add to cart</a>
			</div>
			<div class="image">
				<a href="${ti.url}/sorter" id="next-arrow" class="moveto ${i+1} ${i}"><img src="www/images/tango-icons/go-next.png"/></a> 
				<a href="${ti.url}/sorter" id="prev-arrow" class="moveto ${i-1} ${i}"><img src="www/images/tango-icons/go-previous.png"/></a> 
				<a href="${ti.url}"><img alt="" src="${ti.media.thumbnail}"/></a>
			</div>
			<div class="spacer"></div>
			<h5>${ti.title}</h5>
			<h5 class="collection_name">${ti.collection_name}</h5>
			<a href="${ti.url}/sorter" class="moveto ${i+5} ${i}"><img src="www/images/tango-icons/go-down.png"/></a> 
		</td>
		{if (i)%5 == 0}
		</tr><tr>
		{/if}
		{/for}
		<td class="blank" colspan="0">&nbsp;</td>
	</tr>
</textarea>
{/literal}
