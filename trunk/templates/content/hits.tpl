<div class="content full" id="browse">

{if $msg}
<div class="msg">{$msg}</div>
{/if}

<h1>{$count} items found for '{$query}' in {$collection->collection_name}</h1>

{foreach item=item from=$items}
{$item.img}
{/foreach}

</div>
