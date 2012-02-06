{extends file="layout.tpl"}

{block name="content"}
<div class="controls">
	<a href="set/{$set->name}.json">view json</a> | 
	<a href="set/list">list sets</a>
</div>
<h1>Set {$set->title}</h1>
<table class="items">
	<tr>
		<th></th>
		<th>name</th>
		<th>title</th>
		<th>created</th>
		<th>created by</th>
		<th>add/remove</th>
	</tr>
	{foreach item=item from=$has_items}
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
			<form action="set/{$set->id}/remove" method="post">
				<input type="hidden" name="item_id" value="{$item->id}">
				<input type="submit" value="remove">
			</form>
		</td>
	</tr>
	{/foreach}
</table>
<table class="items">
	<tr>
		<th></th>
		<th>name</th>
		<th>title</th>
		<th>created</th>
		<th>created by</th>
		<th>add/remove</th>
	</tr>
	{foreach item=item from=$not_items}
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
			<form action="set/{$set->id}" method="post">
				<input type="hidden" name="item_id" value="{$item->id}">
				<input type="submit" value="add">
			</form>
		</td>
	</tr>
	{/foreach}
</table>
{/block}
