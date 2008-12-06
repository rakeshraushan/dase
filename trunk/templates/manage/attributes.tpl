{extends file="manage/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Attributes for {$collection->collection_name}</h1>
</div>
<div id="collectionData">
	<a href="manage/{$collection->ascii_id}/attribute/form" class="hide" id="attribute_form_link"></a>
	<a href="manage/{$collection->ascii_id}/attributes.json" class="hide" id="attribute_data_link"></a>
	<div id="browseColumns">
		<div id="catColumn">
			<h4>Attributes:</h4>
		{foreach item=a from=$attributes}
			<a
				href="manage/{$collection->ascii_id}/attribute/{$a->ascii_id}" class="attribute {$a->ascii_id}">{$a->attribute_name}</a>
		{/foreach}
		</div>
	</div>
</div>
<div class="spacer"></div>
{/block} 


