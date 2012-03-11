{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/js/jquery.js"></script>
<script type="text/javascript" src="www/js/jquery.html5uploader.js"></script>
<script type="text/javascript" src="www/js/dase/uploader.js"></script>
{/block}

{block name="title"}DASe: Create New Item{/block} 

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Create New Item(s)</h1>
</div>
<div class="uploader">
	<h2>Upload Files</h2>
	<form action="manage/{$collection->ascii_id}" method="post" enctype="multipart/form-data">
		<!--
		<label for="title">title</label>
		<p>
		<input type="text" name="title"/>
		</p>
		<label for="uploaded_file">attach file(s)</label>
		<input type="file" name="uploaded_file" size="50"/>
		-->
		<p>
		<input id="multiple" type="file" size="50" multiple>
		</p>
		<!--
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
		<input type="submit" value="Upload Files"/>
		</p>
		-->
	</form>
	<h3 class="hide" id="uploadMsg">uploading...</h3>
	<ul id="upload"></ul>
</div>
<ul id="recent"></ul>
{/block}
