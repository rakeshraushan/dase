{extends file="layout.tpl"}

{block name="content"}
<div>
	<h1>Create Content</h1>
	<form action="admin/create" method="post" enctype="multipart/form-data">
		<label for="title">title</label>
		<input type="text" name="title"/>
		<label for="body">body</label>
		<textarea name="body"></textarea>
		<label for="uploaded_file">select a file</label>
		<input type="file" name="uploaded_file"/>
		<p>
		<input type="submit" value="create/upload"/>
		</p>
	</form>
</div>

<h3>Content</h3>
<table class="uploads">
	<tr>
		<th></th>
		<th>name</th>
		<th>title</th>
		<th>created</th>
		<th>created by</th>
		<th>file</th>
		<th>edit</th>
		<th>json</th>
	</tr>
	{foreach item=item from=$items}
	<tr>
		<td class="thumb">
			<a href="item/{$item->id}/edit"><img src="{$item->thumbnail_url}"></a>
		</td>
		<td>
			{$item->name}
		</td>
		<td>
			{$item->title}
		</td>
		<td>
			{$item->created|date_format:'%Y-%m-%d %H:%M'}
		</td>
		<td>
			{$item->created_by}
		</td>
		<td>
			<a href="{$item->file_url}">{$item->file_url}</a>
		</td>
		<td>
			<a href="item/{$item->id}/edit">edit</a>
		</td>
		<td>
			<a href="{$item->url}.json">json</a>
		</td>
	</tr>
	{/foreach}
</table>
{/block}
