{extends file="admin/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/category_scheme_form.js"></script>
{/block}

{block name="title"}DASe: Add a Category Scheme{/block} 

{block name="content"}
<div class="full" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Add a Category Scheme:</h1>
	<form class="adminForm" action="admin/category_schemes" method="post">
		<p>
		<label for="name">Name:</label>
		<input type="text" name="name" value=""/>
		</p>
		<p>
		<label for="scheme">Scheme URI:<br/>http://daseproject.org/category/</label>
		<input type="text" name="uri" value=""/>
		</p>
		<p>
		<label for="fixed">Fixed:</label>
		<p>
		<input checked="checked" type="radio" name="fixed" value="0"> no 
		</p>
		<p>
		<input type="radio" name="fixed" value="1"> yes
		</p>
		<p>
		<label for="applies_to">Applies To:</label>
		<select name="applies_to">
			<option value="">select one:</option>
			<option>attribute</option>
			<option>collection</option>
			<option>item</option>
			<option>set</option>
			<option>set_item</option>
			<option>user</option>
		</select>
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
	<table id="schemes">
		<tr>
			<th>name</th>
			<th>uri</th>
			<th>fixed</th>
			<th>applies to</th>
			<th>description</th>
			<th>created</th>
			<th>created by</th>
			<th></th>
		</tr>
		{foreach item=category_scheme from=$category_schemes->entries}
		<tr>
			<td>{$category_scheme->title}</td>
			<td><a href="admin/category_scheme/form?uri={$category_scheme->scheme|escape:'url'}">{$category_scheme->scheme}</a></td>
			<td>{$category_scheme->fixed}</td>
			<td>{$category_scheme->appliesTo}</td>
			<td>{$category_scheme->summary}</td>
			<td>{$category_scheme->updated}</td>
			<td>{$category_scheme->authorName}</td>
			<td><a href="{$category_scheme->editLink}" class="delete">delete</a></td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}
