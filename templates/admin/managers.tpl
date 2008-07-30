{extends file="admin/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
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
			<th class="rows">{$m->name}</th>
			<td>{$m->dase_user_eid}</td>
			<td>{$m->auth_level}</td>
			<td>{$m->expiration}</td>
			<td>{$m->created}</td>
		</tr>
		{/foreach}
		<form action="admin/{$collection->ascii_id}/managers" method="post">
			<tr>
				<th class="rows"></th>
				<td>
					<input type="text" name="dase_user_eid"/>
				</td>
				<td>
					<select name="auth_level">
						<option value="">select one:</option>
						<option value="none">read</option>
						<option value="metadata">write</option>
						<option value="superuser">admin</option>
					</select>
				</td>
				<td>expiration control</td>
				<td>
					<input type="submit" value="add"/>
				</td>
			</tr>
		</form>
	</table>
</div>
{/block} 


