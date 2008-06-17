{extends file="admin/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	<h1>Managers for {$collection->collection_name}</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="collectionData">
	<table class="dataDisplay">
		<tr>
			<th>Name</th>
			<th>Eid</th>
			<th>Auth Level</th>
			<th>Expiration</th>
			<th>Created</th>
		</tr>
		{foreach item=m from=$managers}
		<tr>
			<th class="rows">{$m.name}</th>
			<td>{$m.dase_user_eid}</td>
			<td>{$m.auth_level}</td>
			<td>{$m.expiration}</td>
			<td>{$m.created}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/block} 


