{extends file="admin/layout.tpl"}

{block name="title"}DASe: Add a Category{/block} 

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Add a Category:</h1>
	<form class="adminForm" action="admin/categories" method="post">
		<p>
		<label for="term">Term:</label>
		<input type="text" name="term"/>
		</p>
		<p>
		<label for="scheme">Scheme:</label>
		<input type="text" name="scheme" value=""/>
		</p>
		<p>
		<label for="label">Label:</label>
		<input type="text" name="label" value=""/>
		</p>
		<p>
		<input type="submit" value="create"/>
		</p>
	</form>
	<h2>Existing Categories</h2>
	<table>
		<tr>
			<th>term</th>
			<th>scheme</th>
			<th>label</th>
		</tr>
		{foreach item=cat from=$cats}
		<tr>
			<td>{$cat->term}</td>
			<td>{$cat->scheme}</td>
			<td>{$cat->label}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}
