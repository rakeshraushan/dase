{extends file="layout.tpl"}

{block name="content"}
<div>
	<div class="controls">
		<a href="item/{$item->id}/edit">edit item</a> |
		<a href="item/{$item->name}.json">item json</a>
	</div>
	<h1>Item {$item->name}</h1>
	<dl id="item">
		<dt>title</dt>
		<dd>{$item->title}</dd>
		<dt>name</dt>
		<dd>{$item->name}</dd>
		<dt>body</dt>
		<dd>{$item->body}</dd>
		{if $item->file_url}
		<dt>thumbnail</dt>
		<dd><img src="{$item->thumbnail_url}"></dd>
		<dt>file</dt>
		<dd><a href="{$item->file_url}">{$item->file_url}</a></dd>
		<dt>file mime type</dt>
		<dd>{$item->mime}</dd>
		<dt>file size</dt>
		<dd>{$item->filesize}</dd>
		{/if}
		{if $item->width}
		<dt>width</dt>
		<dd>{$item->width}</dd>
		{/if}
		{if $item->height}
		<dt>height</dt>
		<dd>{$item->height}</dd>
		{/if}
		<dt>created</dt>
		<dd>{$item->created|date_format:"%D"}</d>
		<dt>created by</dt>
		<dd>{$item->created_by}</d>
		<dt>updated</dt>
		<dd>{$item->updated|date_format:"%D"}</d>
		<dt>updated by</dt>
		<dd>{$item->updated_by}</d>
	</dl>
</div>

{/block}
