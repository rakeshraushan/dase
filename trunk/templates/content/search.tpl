<div class="content full" id="browse">

{if $msg}
<div class="alert">{$msg}</div>
{/if}

<h1>Search Result {$collection->collection_name}</h1>

<form id="search" action="{$collection->ascii_id}/search" method="get">
<!--<input type="hidden" name="collection_ascii_id" value="{$collection->ascii_id}"/>-->
search terms:<br/>
<input type="text" size="40" name="q[]">
in
{if $collection->item_type_array|@count}
<select name="type">
<option value="">all item types</option>
{foreach item=type from=$collection->item_type_array}
<option value="{$collection->ascii_id}::{$type.ascii_id}">{$type.name}</option>
{/foreach}
</select>
{/if}
<select class="dynamic">
<option value="q[]">all fields</option>
{foreach item=attr from=$collection->attribute_array}
<option value="{$collection->ascii_id}::{$attr.ascii_id}[]">{$attr.attribute_name}</option>
{/foreach}
</select>
<input type="submit" value="go">
</form>

<div class="itemGrid">


{foreach item=item from=$items}
<div class="gridItem">
<a href="{$collection->ascii_id}/{$item->serial_number}">
<img src="{$item->thumbnail->url}" alt="file this in w/ simple title"/>
</a>
<h4>{$item->serial_number}</h4>
<h4 class="collName">[{$collection->collection_name}]</h4>
</div>
{/foreach}

</div> <!-- close itemGrid -->

<div class="spacer"></div>
<br/>&nbsp;<br/>
</div>
