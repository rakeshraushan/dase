<div class="content list" id="browse">
<a id="content" name="content"></a>

<div id="msg" class="alert{if !$msg} hide{/if}">{$msg}</div>

{$last_search}

<div class="searchBoxLabel">Search selected collection(s):</div> 
<form id="searchCollections" method="get" action="search">
<div>
<input type="text" name="q[]" size="30">
<input type="hidden" name="from_home_page" value="1">
<input type="submit" value="Search" class="button">
</div>

<ul id="collectionList" class="pageList multicheck">

{foreach item=coll from=$collections}
<li>
<input type="checkbox" name="cols[]" value="{$coll.id}" checked="checked">
<a href="{$coll.ascii_id}" class="checkedCollection">{$coll.collection_name}</a>
<span class="tally" id="tally-{$coll.ascii_id}"></span>
</li>
{/foreach}

<li id="specialAccessLabel" class="label hide">Special Access Collections</li>
</ul>
</form>

<h3 class="browsePublicTags"><a href="action/list_public_tags/">Browse Public User Collections/Slideshows</a></h3>
</div><!-- closes class=standardListContent id=home--> 
