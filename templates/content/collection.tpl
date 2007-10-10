<!-- data for javascript -->
<div id="eid" class="{$user->eid}"></div>
<div id="collectionAsciiId" class="{$collection->ascii_id}"></div>
<!-- end data for javascript -->

<div class="content full" id="browse">

{if $msg}
<div class="alert">{$msg}</div>
{/if}

<h2>{$collection->collection_name} ({$collection->item_count} items)</h2>
<div id="description">{$collection->description}</div>
<h3>Search:</h3>

<form name="searchCollections" id="searchForm" method="get" action="search">
<input type="hidden" name="collection_id" value="{$collection->id}"/>
<input type="text" id="searchQuery" onkeyup="getTypeAhead(this.value,{$collection->id})" name="query" size="30" autocomplete="off"/>
<input type="submit" value="go" class="button"/>
{if $cb && $user->recent_search}
<br/>
<a href="view/recent_searches">View Recent Searches</a>
{/if}

</form>
<div id="autocomplete"></div>

<div id="browseColumns">
<h3>Browse:</h3>

<div id="catColumn">
<h4>Select Attribute Group:</h4>
<a href="x/{$collection->ascii_id}/attributes/public" class="spill">Collection Attributes</a>
<a href="x/{$collection->ascii_id}/attributes/admin">Admin Attributes</a>
</div>

<div id="attColumn" class="ajax/{$collection->ascii_id}/attributes/public"></div>

<div id="valColumn"></div>


</div> <!-- close browseColumns -->
<div class="spacer"></div>
</div> <!-- close content -->
