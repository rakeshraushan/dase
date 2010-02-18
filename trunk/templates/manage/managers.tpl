{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/js/dase/managers.js"></script>
{/block}

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Managers for {$collection->collection_name}</h1>
</div>
<div id="collectionData">
	<table class="dataDisplay" id="managers">
		<tr>
			<th>Name</th>
			<th>Eid</th>
			<th>Auth Level</th>
			<th>Expiration</th>
			<th>Created</th>
			<th></th>
		</tr>
		{foreach item=m from=$managers}
		<tr>
			<th class="rows">{$m->name}</th>
			<td>{$m->dase_user_eid}</td>
			<td>{$m->auth_level}</td>
			<td>{$m->expiration}</td>
			<td>{$m->created}</td>
			<td>
				<a href="manage/{$collection->ascii_id}/managers/{$m->dase_user_eid}" class="delete manager">delete</a>
			</td>
		</tr>
		{/foreach}
		<form action="manage/{$collection->ascii_id}/managers" method="post">
			<tr>
				<th class="rows"></th>
				<td>
					<input type="text" name="dase_user_eid"/>
				</td>
				<td>
					<select name="auth_level">
						<option value="">select one:</option>
						<option value="read">read</option>
						<option value="write">write</option>
						<option value="admin">admin</option>
					</select>
				</td>
				<td>expiration control</td>
				<td>
				</td>
				<td>
					<input type="submit" value="add"/>
				</td>
			</tr>
		</form>
	</table>
</div>
{/block} 


