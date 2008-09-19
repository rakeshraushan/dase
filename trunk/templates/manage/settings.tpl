{extends file="manage/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Collection Settings for {$collection->collection_name}</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="collectionData">
	<form action="manage/{$collection->ascii_id}/settings" method="post">
	<table class="dataDisplay">
		<tr>
			<th>Name</th>
			<td class="data"><input type="text" name="collection_name" size="40" value="{$collection->collection_name}"/></td>
		</tr>
		<tr>
			<th>Ascii Id</th>
			<td class="data">{$collection->ascii_id}</td>
		</tr>
		<tr>
			<th>Is Public
				<div class="current">
				{if 0 == $collection->is_public}not public{/if}
				{if 0 != $collection->is_public}public{/if}
				</div>
			</th>
			<td class="data">
				<p>
				<input type="radio" name="is_public" value="false" {if 0 == $collection->is_public}checked="checked"{/if}/> no
				</p>
				<p>
				<input type="radio" name="is_public" value="true" {if 0 != $collection->is_public}checked="checked"{/if}/> yes
				</p>
			</td>
		</tr>
		<tr>
			<th>Visibility (read/write)
				<div class="current">
				{$collection->visibility}
				</div>
			</th>
			<td class="data">
				<select name="visibility">
					<option {if $collection->visibility eq 'manager'}selected="selected"{/if} value="manager">manager</option>
					<option {if $collection->visibility eq 'user'}selected="selected"{/if} value="user">user</option>
					<option {if $collection->visibility eq 'public'}selected="selected"{/if} value="public">public</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>Description
				<div class="current">
				{$collection->description}
				</div>
			</th>
			<td class="data">
				<textarea name="description" cols="40" rows="4">{$collection->description}</textarea>
			</td>
		</tr>
		<tr>
			<th>Created</th>
			<td class="data">{$collection->created}</td>
		</tr>
		<tr>
			<th></th>
			<td class="data">
				<input type="submit" value="update"/>
			</td>
		</tr>
	</table>
</div>
{/block} 


