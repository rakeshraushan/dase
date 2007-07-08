<div class="content list" id="home">
{if $msg}
<div class="msg">{$msg}</div>
{/if}
<h1>List of attributes for {$collection->collection_name}</h1>
<ul>
{foreach item=attribute from=$attributes}
<li>{$attribute['attribute_name']}</li>
{/foreach}
</ul>
</div>
