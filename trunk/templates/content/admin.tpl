<div class="content list" id="browse">
<a id="content" name="content"></a>

<noscript>
<h1 class="alert">For the most satisfying DASE experience we recommend that Javascript be enabled.</h1>
</noscript>
{if $msg}
<div class="alert">{$msg}</div>
{/if}

<form id="batch_update" class="styled" action="admin/build_index" method="get">
<h2>rebuild search indexes</h2>
<p>
<select name="collection_ascii_id">
<option value="">select a collection</option>
{foreach item=collection from=$collections}
<option value="{$collection->ascii_id}">{$collection->collection_name}</option>
{/foreach}
</select>
</p>
<p>
<input type="submit" value="rebuild search index">
</p>
</form>


</div><!-- closes class=standardListContent id=home--> 
