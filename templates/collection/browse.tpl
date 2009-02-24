{extends file="layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/htmlbuilder.js"></script>
<script type="text/javascript" src="www/scripts/dase/collection_browse.js"></script>
{/block}

{block name="title"}DASe: {$collection->name|escape}{/block} 
{block name="servicedoc"}
<link rel="service" type="application/atomsvc+xml" href="collection/{$collection->asciiId}/service"/>
{/block} 

{block name="content"}
<div class="full" id="browse">
	{if $msg}<h3 class="msg">{$msg}</h3>{/if}
	<div id="collectionAsciiId" class="hide">{$collection->asciiId}</div>
	<div class="contentHeader">
		<h2 class="collectionLink">{$collection->name|escape} ({$collection->itemCount} items)</h2>
		<h3 class="collectionDescription">{$collection->description|escape}</h3>
	</div>
	<form method="get" action="search">
		<div>
			<h3 class="utilLabel">Search:</h3>
			<input type="text" id="queryInput" name="q" size="30"/>
			<input type="hidden" name="collection_ascii_id" value="{$collection->asciiId}"/>
			<!--
			<select id="attributesSelect" class="hide"></select>
			-->
			<input type="submit" value="go" class="button"/>
		</div>
	</form>
	<h3 class="utilLabel">Browse:</h3>
	<div id="browseColumns">
		<div id="catColumn">
			<h4>Select Attribute Group:</h4>
			<a href="collection/{$collection->asciiId}/attributes" id="collectionAtts" class="spill">Collection Attributes</a>
			<a href="collection/{$collection->asciiId}/admin_attributes">Admin Attributes</a>
		</div>
		<div id="attColumn"><!-- insert template output--></div>
		<div id="valColumn" class="hide"><!--insert template output--></div>

		<!-- javascript template -->
		<textarea class="javascript_template" id="vals_jst">
			{literal}
			<h4>Select ${att_name} Value:</h4>
			<ul>
				{for v in values}
				<li>
				<a href="search?${coll}.${att_ascii}=${encodeURIComponent(v.v)}" class="val_link">${v.v} <span class="tally">(${v.t})</span></a> 
				</li>
				{/for}
			</ul>
			{/literal}
		</textarea>
		<!-- end javascript template -->

	</div> <!-- close browseColumns -->
	<div class="spacer"></div>
</div> <!-- close class full -->
{/block}

