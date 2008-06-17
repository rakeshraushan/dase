{extends file="admin/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	<h1>Attributes for {$collection->collection_name}</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="collectionData">
	<table id="attributesTable" class="dataDisplay">
		<tr>
			<th>Name</th>
			<th>Ascii Id</th>
			<th>Updated</th>
			<th>Usage Notes</th>
			<th>Input Type</th>
			<th>Sort Order</th>
			<th>In Basic Search</th>
			<th>On List Display</th>
			<th>Is Public</th>
		</tr>
		{foreach item=a from=$attributes}
		<tr>
			<th class="rows"><a href="attribute/{$collection->ascii_id}/{$a->ascii_id}" class="attribute {$a->ascii_id}">{$a->attribute_name}</a></th>
			<td class="data">{$a->ascii_id}</td>
			<td class="data">{$a->updated}</td>
			<td class="data">{$a->usage_notes}</td>
			<td class="data">{$a->html_input_type}</td>
			<td class="data">
				<input type="text" size="{$a->sort_order|count_characters}" value="{$a->sort_order}"/>
			</td>
			<td>
				{if 1 == $a->in_basic_search}
				<input type="checkbox" name="in_basic_search_{$a->ascii_id}" checked="checked"/>
				{else}
				<input type="checkbox" name="in_basic_search_{$a->ascii_id}"/>
				{/if}
			</td>
			<td>
				{if 1 == $a->is_on_list_display}
				<input type="checkbox" name="is_on_list_display_{$a->ascii_id}" checked="checked"/>
				{else}
				<input type="checkbox" name="is_on_list_display_{$a->ascii_id}"/>
				{/if}
			</td>
			<td>
				{if 1 == $a->is_public}
				<input type="checkbox" name="is_public_{$a->ascii_id}" checked="checked"/>
				{else}
				<input type="checkbox" name="is_public_{$a->ascii_id}"/>
				{/if}
			</td>
		</tr>
		<tr class="hide" id="editRow-{$a->ascii_id}">
		</tr>
		{/foreach}
	</table>
</div>
{/block} 


