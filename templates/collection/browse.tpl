{extends file="layout.tpl"}

{block name="head"}
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
		<h1>{$collection->name|escape} ({$collection->itemCount} items)</h1>
		<h3>{$collection->description|escape}</h3>
	</div>
	<form method="get" action="search">
		<div>
			<input type="text" id="queryInput" name="q" size="30"/>
			<input type="hidden" name="collection_ascii_id" value="{$collection->asciiId}"/>
			<!--
			<select id="attributesSelect" class="hide"><!-- filled ajaxily --></select>
			-->
			<input type="submit" value="Search" class="button"/>
		</div>
	</form>
	<div id="browseColumns">
		<h3>Browse:</h3>
		<div id="catColumn">
			<h4>Select Attribute Group:</h4>
			<a href="collection/{$collection->asciiId}/attributes/public" id="collectionAtts" class="spill">Collection Attributes</a>
			<a href="collection/{$collection->asciiId}/attributes/admin">Admin Attributes</a>
		</div>
		<div id="attColumn" class="collection/{$collection->asciiId}/attributes/public"><!-- insert template output--></div>

		<!-- javascript template -->
		<textarea class="javascript_template" id="atts_jst">
			{literal}
			<a href="#" id="attSorter">toggle sort</a>
			<h4>Select Attribute:</h4>
			<ul id="attList">
				{for att in atts}
				<li>
				<a href="attribute/${att.collection}/${att.ascii_id}/values.json"
					id="${att.ascii_id}" class="att_link ${att.sort_order}"
					><span class="att_name">${att.attribute_name}</span> <span class="tally" id="tally-${att.ascii_id}"></span></a>
				</li>
				{/for}
			</ul>
			{/literal}
		</textarea>
		<!-- end javascript template -->

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

