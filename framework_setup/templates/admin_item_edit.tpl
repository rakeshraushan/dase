{extends file="layout.tpl"}

{block name="content"}
<div>
	<div class="controls">
		<a href="item/{$item->id}">view item</a>
	</div>
	<h1>Edit Item</h1>
	<form action="item/{$item->id}/edit" method="post">
		<label for="title">title</label>
		<input type="text" name="title" value="{$item->title}"/>
		<label for="body">body</label>
		<textarea name="body">{$item->body}</textarea>
		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
	<h1>Swap in File</h1>
	<form action="item/{$item->id}/swap" method="post" enctype="multipart/form-data">
		<p>
		<label for="uploaded_file">select a file</label>
		<input type="file" name="uploaded_file"/>
		<input type="submit" value="swap in file"/>
		</p>
	</form>
	<img src="{$item->thumbnail_url}">
</div>

{/block}
