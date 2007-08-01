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

<div id="browseColumns" class="{$collection->id} {if $cb}0{else}1{/if}">
<h3>Browse:</h3>

<div id="catColumn">
<h4>Select Attribute Group:</h4>
<a href="{$collection->ascii_id}?cat_id=all" class="{if $display_cat->id == 'all'}spill{else}catLink{/if}" id="catLink_0">Collection Attributes</a>
{foreach item=cat from=$collection->category_array }
{if $cat.id}
<a href="{$collection->ascii_id}?cat_id={$cat.id}" class="{if $display_cat.id == $cat.id}spill{else}catLink{/if}" id="catLink_{$cat.id}">{$cat.name} <span class="tally">({$cat.att_count})</span></a>
{/if}
{/foreach}
<a href="{$collection->ascii_id}?cat_id=admin" class="{if $display_cat->id eq 'admin'}spill{else}catLink{/if}" id="catLink_admin">Admin Attributes</a>
</div>

<!-- what follows will ONLY be in effect if user has javascript disabled-->

<div id="attColumn" {if $attribute->id}class="{$attribute->id}"{/if} >
<h4>Select {$display_cat->name} Attribute:</h4>
<ul id="attList">
{foreach item=att from=$collection->attribute_array}
<li><a href="{$collection->ascii_id}?cat_id={$display_cat->id}&browse_attribute_id={$att.id}" class="{if $attribute->id == $att.id}spill{else}attLink{/if}">
{$att.attribute_name|escape:"html"}</a></li>
{/foreach}
{foreach item=att from=$collection->admin_attribute_array}
<li><a href="{$collection->ascii_id}?cat_id={$display_cat->id}&browse_attribute_id={$att.id}" class="{if $attribute->id == $att.id}spill{else}attLink{/if}">
{$att.attribute_name|escape:"html"}</a></li>
{/foreach}
</div>

<!--<div id="valColumn" {if $attribute->total == 0}class="empty"{/if}>-->
{if count($attribute->display_values) <= 0}
<div id="valColumn" class="empty">
{else}
<div id="valColumn">
{if count($attribute->display_values) == 1}
<h4>There is 1 value for the attribute "{$attribute->attribute_name}."</h4>
{else}
<h4>There are {$attribute->display_values|@count} values for the attribute "{$attribute->attribute_name}."</h4>
{/if}
<ul id="attList">
{foreach item=value from=$attribute->display_values }
<li>
<a href="index.php?action=search&query={$value.urlencoded_value_text}&attribute_id={$attribute->id}&collection_id={$collection->id}&cat_id={$display_cat->id}">{if $value.value_text|count_words < 3}{$value.value_text|truncate:50:'...':true}{else}{$value.value_text}{/if} <span class="tally">({$value.tally})</span></a></li>
{/foreach}
{/if}
</div>

</div> <!-- close browseColumns -->
<div class="spacer"></div>
</div> <!-- close content -->
