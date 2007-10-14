<!-- the AJAX here is not about personalization, REST, etc.
     it is rather just an attempt to make the page faster,
	 so requiring the client ot grab this value from here is OK.
	 This page is useless w/o AJAX anyway...
	 -->
<!-- data for javascript -->
<div id="collectionAsciiId" class="{$collection->ascii_id}"></div>
<!-- end data for javascript -->

<div class="content full" id="browse">

<div id="msg" class="alert{if !$msg} hide{/if}">{$msg}</div>

<h2>{$collection->collection_name} ({$collection->item_count} items)</h2>
<div id="description">{$collection->description}</div>
<h3>Search:</h3>

<form method="get" action="search">
<input type="hidden" name="collection_id" value="{$collection->id}"/>
<input type="text" name="query" size="30"/>
<input type="submit" value="go" class="button"/>
</form>

<div id="browseColumns">
<h3>Browse:</h3>

<div id="catColumn">
<h4>Select Attribute Group:</h4>
<a href="ajax/{$collection->ascii_id}/attributes/public" class="spill">Collection Attributes</a>
<a href="ajax/{$collection->ascii_id}/attributes/admin">Admin Attributes</a>
</div>

<div id="attColumn" class="ajax/{$collection->ascii_id}/attributes/public"></div>

<div id="valColumn" class="hide"></div>


</div> <!-- close browseColumns -->
<div class="spacer"></div>
</div> <!-- close content -->
