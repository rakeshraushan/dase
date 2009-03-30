{extends file="layout.tpl"}

{block name="content"}
<div class="main">
	<table class="listing" id="sortableProps">
		<thead>
			<tr>
				<th>Proposal Name</th>
				<th>Submitter</th>
				<th>Department</th>
				<th>Submitted</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach item=proposal from=$proposals->entries}
			<tr>
				<td>{$proposal->proposal_name.text}</td>
				<td>{$proposal->getParentLinkTitleByItemType('person')}</td>
				<td>{$proposal->department}</td>
				<td>{$proposal->proposal_submitted.text}</td>
				<td><a href="proposal/{$proposal->serialNumber}/eval">view</a></td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/block}

