{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/js/dase/uploader.js"></script>
{/block}

{block name="title"}DASe: Create New Item{/block} 

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Create New Item</h1>
</div>
<div class="uploader">
	<form action="manage/{$collection->ascii_id}" method="post" enctype="multipart/form-data">
		<label for="title">title</label>
		<p>
		<input type="text" name="title"/>
		</p>
		<p>
		<label for="uploaded_file">attach a file</label>
		<input type="file" name="uploaded_file" size="50"/>
		</p>
		<p>
		{if $item_types|@count}
		<select name="item_type">
			<option value="default">Select an Item Type (optional):</option>
			{foreach item=t from=$item_types}
			<option value="{$t->ascii_id}">{$t->name}</option>
			{/foreach}
			<option ="default">none (default)</option>
		</select>
		{/if}
		</p>
		<p>
		<input type="submit" value="Create New Item"/>
		</p>
	</form>
</div>
<ul id="recent"></ul>
{/block}
