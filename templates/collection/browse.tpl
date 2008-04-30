{extends file="layout.tpl"}

{block name="title"}DASe: {$collection->name|escape}{/block} 

{block name="content"}
<div class="full" id="browse">
	{if $msg}<h3 class="msg">{$msg}</h3>{/if}
	<div id="collectionAsciiId" class="hide">{$collection->ascii_id}</div>
	<div class="contentHeader">
		<h1>{$collection->name|escape} ({$collection->item_count})</h1>
		<h3>{$collection->description|escape}</h3>
	</div>
	<h3>Search:</h3>
	<form method="get" action="collection/{$collection->ascii_id}/search">
		<div>
			<input type="text" name="q" size="30"/>
			<input type="submit" value="go" class="button"/>
		</div>
	</form>
	<div id="browseColumns">
		<h3>Browse:</h3>
		<div id="catColumn">
			<h4>Select Attribute Group:</h4>
			<a href="collection/{$collection->ascii_id}/attributes/public" class="spill">Collection Attributes</a>
			<a href="collection/{$collection->ascii_id}/attributes/admin">Admin Attributes</a>
		</div>
		<div id="attColumn" class="collection/{$collection->ascii_id}/attributes/public"></div>

		<div id="valColumn" class="hide"></div>
	</div> <!-- close browseColumns -->
	<div class="spacer"></div>
</div> <!-- close class full -->
{/block}

