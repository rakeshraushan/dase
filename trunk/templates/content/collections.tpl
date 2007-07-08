<div class="content list" id="browse">
<a id="content" name="content"></a>

<noscript>
<h1 class="alert">For the most satisfying DASE experience we recommend that Javascript be enabled.</h1>
</noscript>
{if $msg}
<div class="alert">{$msg}</div>
{/if}

{$last_search}

<form id="searchCollections" method="get" action="search">
<div class="searchBoxLabel">Search selected collection(s):</div> 
<input type="text" name="query" size="30"/>
<input type="hidden" name="from_home_page" value="1"/>
<input type="submit" value="Search" class="button"/>
{if $user->recent_search}
<br/>
<a href="view/recent_searches/">view my recent searches</a>
{/if}
<div id="collectionList">
<ul class="pageList multicheck">

{foreach item=coll from=$collections}
{if $coll.is_public eq 1}
<li><input type="checkbox" name="cols[]" value="{$coll.id}" {if in_array($coll.id,$current_collections)}checked="checked"{/if} />
<a href="{$coll.ascii_id}" id="col_link{$coll.id}" name="collectionLink"
{if in_array($coll.id,$current_collections)}class="checkedCollection"{/if}>{$coll.collection_name}</a>
<span class="tally" id="tally-{$coll.id}"></span>
</li>
{/if}
{/foreach}
<!--</ul>-.
<h3>Special Access Collections</h3>
<!--<ul class="pageList">-.
{foreach item=np_coll from=$collections}
{if $np_coll.is_public ne 1}
<li><input type="checkbox" name="cols[]" value="{$np_coll.id}" {if in_array($coll.id,$current_collections)}checked="checked"{/if} />
<a href="{$np_coll.ascii_id}" id="col_link{$np_coll.id}" name="collectionLink"
{if in_array($np_coll.id,$current_collections)}class="checkedCollection"{/if}>{$np_coll.collection_name}</a>
<span class="tally" id="tally-{$np_coll.id}"></span>
</li>
{/if}
{/foreach}
</ul>
</div>

<h3><a href="action/list_public_tags/">Browse Public User Collections/Slideshows</a></h3>
</div><!-- closes class=standardListContent id=home--> 
