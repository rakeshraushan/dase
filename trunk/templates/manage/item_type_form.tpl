{extends file="manage/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Item Types for {$collection->collection_name}</h1>
</div>
<div id="collectionData">
	<div id="browseColumns">
		<div id="catColumn">
			<h4>Item Types:</h4>
			{foreach item=t from=$item_types}
			<a href="manage/{$collection->ascii_id}/item_type/{$t->ascii_id}"
				{if $type->ascii_id == $t->ascii_id}class="spill"{/if}>{$t->name}</a>
			{/foreach}
			<p class="getForm">
			<a class="delete"
				href="manage/{$collection->ascii_id}/item_type_form"
				>new item type form</a>
			</p>
		</div>
		{if $type}
		<div id="attColumn">
			{/if}
		</div>
	</div>
	<div class="spacer"></div>
</div>
{/block} 


