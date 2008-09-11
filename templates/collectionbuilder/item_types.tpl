{extends file="collectionbuilder/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	<h1>Item Types for {$collection->collection_name}</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="collectionData">
	<table class="dataDisplay">
		<tr>
			<th>Name</th>
			<th>Ascii Id</th>
			<th>Description</th>
			<th>Attributes</th>
		</tr>
		{foreach item=type from=$item_types}
		<tr>
			<th class="rows">{$type->name}</th>
			<td class="data">{$type->ascii_id}</td>
			<td>{$type->description}</td>
			<td class="data">
				<ul>
					{foreach item=a from=$type->atts}
					<li>{$a->attribute_name} ({$a->cardinality})</li>
					{/foreach}
				</ul>
			</td>
		</tr>
		{/foreach}
	</table>
</div>
{/block} 


