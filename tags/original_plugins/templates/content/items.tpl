<div class="content full" id="home">

{if $msg}
<div class="msg">{$msg}</div>
{/if}

<h1>{$count} items found in {$collection->collection_name}</h1>

{html_table loop=$items cols=4 table_attr='class="contactSheet" id="searchResultTable"'}

</div>
