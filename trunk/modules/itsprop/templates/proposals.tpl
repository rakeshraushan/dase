{extends file="layout.tpl"}

{block name="content"}
<div class="main">
	<table class="listing">
		<tr>
			<th>Proposal Name</th>
			<th>Department</th>
			<th></th>
		</tr>
		{foreach item=proposal from=$proposals->entries}
		<tr>
			<td>{$proposal->proposal_name.text}</td>
			<td>{$proposal->department}</td>
			<td><a href="proposal/{$proposal->serialNumber}/preview">preview</a></td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}

