{extends file="admin/layout.tpl"}

{block name="title"}DASe: Add a Category Scheme{/block} 

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Add a Category Scheme:</h1>
	<form class="adminForm" action="admin/category_schemes" method="post">
		<p>
		<label for="name">Name:</label>
		<input type="text" name="name" value=""/>
		</p>
		<p>
		<label for="scheme">Scheme URI:<br/>{$app_root}category</label>
		<input type="text" name="uri" value=""/>
		</p>
		<p>
		<label for="label">Fixed:</label>
		<p>
		<input type="radio" name="fixed" value="0"> no 
		</p>
		<p>
		<input type="radio" name="fixed" value="1"> yes
		</p>
		</p>
		<p>
		<label for="description">Description:</label>
		<textarea name="description" rows="2" cols="30"></textarea>
		</p>
		<p>
		<input type="submit" value="create"/>
		</p>
	</form>
	<h2>Existing Category Schemes</h2>
	<table>
		<tr>
			<th>name</th>
			<th>uri</th>
			<th>fixed</th>
			<th>description</th>
			<th>created</th>
			<th>created by</th>
			<th></th>
		</tr>
		{foreach item=scheme from=$schemes->entries}
		<tr>
			<td>{$scheme->title}</td>
			<td><a href="admin/category_scheme/form?uri={$scheme->scheme|escape:'url'}">{$scheme->scheme}</a></td>
			<td>{$scheme->fixed}</td>
			<td>{$scheme->summary}</td>
			<td>{$scheme->updated}</td>
			<td>{$scheme->authorName}</td>
			<td><a href="xx" class="delete">delete</a></td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}
