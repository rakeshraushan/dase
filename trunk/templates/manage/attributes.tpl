{extends file="manage/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Attributes for {$collection->collection_name}</h1>
	<h3 class="instruction">Click on attribute name to edit.</h3>
</div>
<div id="collectionData">
	<a href="manage/{$collection->ascii_id}/attribute/form" class="hide" id="attribute_form_link"></a>
	<a href="manage/{$collection->ascii_id}/attributes.json" class="hide" id="attribute_data_link"></a>
	<table id="attributesTable" class="dataDisplay">
		<tr>
			<th><a href="manage/{$collection->ascii_id}/attributes?sort=attribute_name">Name</a></th>
			<th><a href="manage/{$collection->ascii_id}/attributes?sort=html_input_type">Input Type</a></th>
			<th>In Basic Search</th>
			<th>On List Display</th>
			<th>Is Public</th>
			<th>Usage Notes</th>
		</tr>
		{foreach item=a from=$attributes}
		<tr>
			<th class="rows"><a href="attribute/{$collection->ascii_id}/{$a->ascii_id}" class="attribute {$a->ascii_id}">{$a->attribute_name}</a>
			</th>
			<td class="data">{$a->html_input_type}</td>
			<td>
				{if 1 == $a->in_basic_search}
				X
				{else}
				{/if}
			</td>
			<td>
				{if 1 == $a->is_on_list_display}
				X
				{else}
				{/if}
			</td>
			<td>
				{if 1 == $a->is_public}
				X
				{else}
				{/if}
			</td>
			<td class="data">{$a->usage_notes}</td>
		</tr>
		<tr class="hide" id="editRow-{$a->ascii_id}">
		</tr>
		{/foreach}
		<tr>
			<td colspan="6" class="data">
				<form action="manage/{$collection->ascii_id}/attributes" method="post">
					<input type="text" name="attribute_name"/>
					<input type="submit" value="add attribute"/>
				</form>
			</td>
		</tr>
	</table>
</div>
{/block} 

