{extends file="manage/layout.tpl"}

{block name="content"}
<table>
	<tr>
		<th>name</th>
		<th>type</th>
		<th>table</th>
	</tr>
{foreach item=index from=$indexes}
	<tr>
		<th>{$index.name}</th>
		<th>{$index.type}</th>
		<th>{$index.table}</th>
	</tr>
{/foreach}
</table>
{/block} 


