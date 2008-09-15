<!-- javascript template for attribute form -->
<td colspan="3" class="editForm">
	{literal}
	<h1>${att.attribute_name} (${att.ascii_id})</h1>
	<form action="manage/${att.collection_ascii_id}/attribute/${att.ascii_id}" method="post">
		<p>
		<label for="attribute_name">Name</label>
		<input type="text" name="attribute_name" value="${att.attribute_name}"/>
		</p>
		<p>
		<label for="usage_notes">Usage Notes</label>
		<textarea type="text" name="usage_notes" >${att.usage_notes}</textarea>
		</p>
		<p>
		<label for="sort_order">Sort Order</label>
		<select name="sort_after">
			{for attname in att.ordered_atts}
			<option value="${attname_index}" {if att.last == attname_index}selected="selected"{/if}>${attname}</option>
			{/for}
		</select>
		</p>
		<input type="hidden" name="att_ascii_id" value="${att.ascii_id}"/>
		<input type="hidden" name="sort" value="${sort}"/>
		<p>
		<label for="input_type">Input Type</label>
		<select name="input_type">
			<option value="text" {if 'text' == att.html_input_type}selected="selected"{/if}>text</option>
			<option value="textarea" {if 'textarea' == att.html_input_type}selected="selected"{/if}>textarea</option>
			<option value="radio" {if 'radio' == att.html_input_type}selected="selected"{/if}>radio</option>
			<option value="checkbox" {if 'checkbox' == att.html_input_type}selected="selected"{/if}>checkbox</option>
			<option value="select" {if 'select' == att.html_input_type}selected="selected"{/if}>select</option>
			<option value="listbox" {if 'listbox' == att.html_input_type}selected="selected"{/if}>list box</option>
			<option value="no_edit" {if 'no_edit' == att.html_input_type}selected="selected"{/if}>no_edit</option>
			<option value="text_with_menu" {if 'text_with_menu' == att.html_input_type}selected="selected"{/if}>text_with_menu</option>
		</select>
		</p>
		<p>
		<input type="checkbox" {if 1 == att.in_basic_search}checked="checked"{/if} name="in_basic_search"> In Basic Search 
		</p>

		<p>
		<input type="checkbox" {if 1 == att.is_on_list_display}checked="checked"{/if} name="is_on_list_display"> On List Display 
		</p>

		<p>
		<input type="checkbox" {if 1 == att.is_public}checked="checked"{/if} name="is_public"> Is Public 
		</p>
		<p class="deleteControl">
		<input type="submit" name="method" id="deleteAtt" value="delete attribute"/>
		</p>
		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
	{/literal}
</td>
<td colspan="3" class="editForm">
	{literal}
	{if 'radio' == att.html_input_type || 'select' == att.html_input_type || 'checkbox' == att.html_input_type}
	<h1>${att.attribute_name} Defined Values</h1>
	<ul class="defined" id="defined_values_list">
		{for val in att.values}
		<li>${val}</li>
		{/for}
	</ul>
	<form id="defined_values_form" action="manage/${att.collection_ascii_id}/attribute/${att.ascii_id}/defined_values" method="post">
		<textarea rows="${att.count}" id="defined_values_input"  name="defined_values_input">{for val in att.values}${val}
			{/for}</textarea> <p class="submitControl">
		<input type="submit" value="update"/>
		</p>
		<!--
		<p>
		<input type="submit" value="done"/>
		</p>
		-->
	</form>
	{/if}
	{/literal}
</td>


