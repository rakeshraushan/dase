<?php

$coll = new Dase_DB_Collection;
$coll->orderBy('collection_name');
$coll->is_public = 1;
$coll_list = '';
foreach ($coll->findAll() as $c) {
	$id = $c['id'];
	$ascii = $c['ascii_id'];
	$name = htmlspecialchars($c['collection_name']);

$coll_list .=<<<EOF
<li id="$ascii">
<input type="checkbox" name="cols[]" value="$id" checked="checked"/>
<a href="$ascii" class="checkedCollection">$name</a>
<span class="tally"></span>
</li>
EOF;

}

$hide = $msg ? 'hide' : '';

$html =<<<EOF
<div class="content list" id="browse">
<a id="content" name="content"></a>

<div id="msg" class="alert $hide">$msg</div>

<div class="searchBoxLabel">Search selected collection(s):</div> 
<form id="searchCollections" method="get" action="search">
<div>
<input type="text" name="q[]" size="30"/>
<input type="hidden" name="from_home_page" value="1"/>
<input type="submit" value="Search" class="button"/>
</div>

<ul id="collectionList" class="pageList multicheck">

$coll_list

<li id="specialAccessLabel" class="label hide">Special Access Collections</li>
</ul>
</form>

<h3 class="browsePublicTags"><a href="action/list_public_tags/">Browse Public User Collections/Slideshows</a></h3>
</div><!-- closes class=standardListContent id=home--> 
EOF;

$tpl = new Dase_Html_Template;
$tpl->setText($html);
$tpl->display();
