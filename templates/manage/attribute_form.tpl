{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/attribute_form.js"></script>
{/block}

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Attributes for {$collection->collection_name}</h1>
</div>
<div id="collectionData">
	<div id="browseColumns">
		<div id="catColumn">
			<h4>Attributes:</h4>
			{foreach item=a from=$attributes}
			<a
				href="manage/{$collection->ascii_id}/attribute/{$a->ascii_id}"
				{if $att->ascii_id == $a->ascii_id}class="spill"{/if}>{$a->attribute_name}</a>
			{/foreach}
			<p class="getForm">
			<a class="delete"
				href="manage/{$collection->ascii_id}/attribute_form"
				>new attribute form</a>
			</p>
		</div>
		{if $att}
		<div id="attColumn">
			{if 'radio' == $att->html_input_type || 
			'select' == $att->html_input_type || 
			'checkbox' == $att->html_input_type}
			<div class="pageControls">
				<a href="#"
					id="toggleAttributeEditForm">hide/show form</a>
			</div>
			{/if}
			{if $att->ascii_id}
			<h1>{$att->attribute_name} ({$att->ascii_id})</h1>
			{else}
			<h1>Create An Attribute</h1>
			{/if}
			<form
				id="editAttribute"
				{if $att->ascii_id}
				action="manage/{$collection->ascii_id}/attribute/{$att->ascii_id}" 
				{else}
				action="manage/{$collection->ascii_id}/attributes" 
				{/if}
				method="post">
				<p>
				<label for="attribute_name">Name</label>
				<input type="text" name="attribute_name" value="{$att->attribute_name}"/>
				</p>
				<p>
				<label for="usage_notes">Usage Notes
					<br/>
					<span class="current">
						[{$att->usage_notes}]
					</span>
				</label>
				<textarea type="text" name="usage_notes" >{$att->usage_notes}</textarea>
				</p>
				<p>
				<label for="sort_order">Sort Order</label>
				<select name="sort">
					{foreach item=oa from=$ordered name=ord}
					<option value="{$smarty.foreach.ord.iteration}" {if $att->sort_order == $smarty.foreach.ord.iteration}selected="selected"{/if}>{$oa}</option>
					{/foreach}
				</select>
				</p>
				<input type="hidden" name="att_ascii_id" value="{$att->ascii_id}"/>
				<p>
				<label for="input_type">Input Type</label>
				<select name="input_type">
					<option value="text" 
					{if 'text' == $att->html_input_type}selected="selected"{/if}>text
					</option>
					<option value="textarea" 
					{if 'textarea' == $att->html_input_type}selected="selected"{/if}>textarea
					</option>
					<option value="radio" 
					{if 'radio' == $att->html_input_type}selected="selected"{/if}>radio
					</option>
					<option value="checkbox" {
					if 'checkbox' == $att->html_input_type}selected="selected"{/if}>checkbox
					</option>
					<option value="select" 
					{if 'select' == $att->html_input_type}selected="selected"{/if}>select
					</option>
					<option value="listbox" 
					{if 'listbox' == $att->html_input_type}selected="selected"{/if}>list box
					</option>
					<option value="no_edit" 
					{if 'no_edit' == $att->html_input_type}selected="selected"{/if}>no_edit
					</option>
					<option value="text_with_menu" 
					{if 'text_with_menu' == $att->html_input_type}selected="selected"{/if}>text_with_menu
					</option>
				</select>
				</p>
				<p>
				<input type="checkbox" {if 1 == $att->in_basic_search}checked="checked"{/if} name="in_basic_search"> In Basic Search 
				{if
				$att->in_basic_search}
				<span class="checkmark">&#10003;</span>
				{/if} 
				</p>

				<p>
				<input
				type="checkbox"
				{if 1 == $att->is_on_list_display}checked="checked"{/if}
				name="is_on_list_display">
				On List Display
				{if $att->is_on_list_display} 
				<span class="checkmark">&#10003;</span>
				{/if}
				</p>

				<p>
				<input type="checkbox" 
				{if 1 == $att->is_public}checked="checked"{/if} name="is_public"> Is Public 
				{if
				$att->is_public} 
				<span class="checkmark">&#10003;</span>
				{/if}
				</p>
				<p>
				{if $att->ascii_id}
				<input type="submit" value="update"/>
				<input
				type="submit"
				name="method"
				id="deleteAtt"
				class="deleteControl"
				value="delete {$att->attribute_name}"/>
				{else}
				<input type="submit" value="create"/>
				{/if}
				</p>
			</form>
			{if 'radio' == $att->html_input_type || 
			'select' == $att->html_input_type || 
			'checkbox' == $att->html_input_type}
			<div id="definedVals">
				<h1>{$att->attribute_name} Defined Values</h1>
				<form
					id="defined_values_form"
					action="manage/{$collection->ascii_id}/attribute/{$att->ascii_id}/defined_values.json" method="post">
					<textarea
						rows="{$defined_values|@count}"
						id="defined_values_input"
						name="defined_values_input"></textarea> 
					<p>
					<input type="submit" value="update"/>
					</p>
					<!--
					<p>
					<input type="submit" value="done"/>
					</p>
					-->
				</form>
				<form>
					<h3>sample form input</h3>
					<div class="defined" id="defined_values_sample"></div>
				</form>
			</div>
			{/if}
		</div>
		{/if}
	</div>
</div>
<div class="spacer"></div>
<!-- javascript templates for defined values-->
<textarea class="javascript_template" id="inp_select_jst">
	{literal}
	<select>
	<option>select one:</option>
	{for v in defined}
	<option>${v}</option>
	{/for}
	</select>
	{/literal}
</textarea>
<textarea class="javascript_template" id="inp_radio_jst">
	{literal}
	{for v in defined}
	<p>
	<input type="radio" name="sample"> ${v}
	</p>
	{/for}
	{/literal}
</textarea>
<textarea
	class="javascript_template"
	id="inp_checkbox_jst">
	{literal}
	{for v in defined}
	<p>
	<input type="checkbox" name="sample"> ${v}
	</p>
	{/for}
	{/literal}
</textarea>
<!-- end javascript template -->
{/block}

