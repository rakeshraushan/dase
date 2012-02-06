{extends file="layout.tpl"}

{block name="content"}

<h3>Items</h3>
<table class="items">
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
			<a href="item/{$item->id}"><img src="{$item->thumbnail_url}"></a>
		</td>
		<td>
			{$item->name}
		</td>
		<td>
			{$item->title}
		</td>
		<td>
			{$item->created|date_format:'%D'}
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
