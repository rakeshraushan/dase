{extends file="admin/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	<h1>Collection Settings for {$collection->collection_name}</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="collectionData">
	<table class="dataDisplay">
		<tr>
			<th>Name</th>
			<th>Ascii Id</th>
			<th>Is Public</th>
			<th>Description</th>
			<th>Created</th>
			<th>Path to Media Files</th>
		</tr>
		<tr>
			<th>{$collection->collection_name}</th>
			<td>{$collection->ascii_id}</td>
			<td>
				{if 0 == $collection->is_public}no{else}yes{/if}
			</td>
			<td>{$collection->description}</td>
			<td>{$collection->created}</td>
			<td>{$collection->path_to_media_files}</td>
		</tr>
	</table>
</div>
{/block} 


