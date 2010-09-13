{extends file="layout.tpl"}

{block name="head-meta"}
<meta name="item-title" content="{$item->entry->_title}">
{if 'set' == $item->tagType}
<meta name="tagOwner" content="{$item->authorName}">
{/if}
{/block}

{block name="head-links"}
{if $item->entry->editLink}
<!-- atompub -->
<link rel="edit" type="application/atom+xml" href="{$item->entry->editLink}">
<link rel="service" type="application/atomsvc+xml" href="{$item->entry->serviceLink}">
<link rel="http://daseproject.org/relation/edit" type="application/json" href="{$item->entry->jsonEditLink}">
<link rel="http://daseproject.org/relation/attributes" type="application/json" href="{$item->entry->attributesLink}">
<link rel="http://daseproject.org/relation/input_template" type="text/html" href="{$item->entry->alternateLink}/input_template">
{/if}
{/block}

{block name="head"}
<script type="text/javascript" src="www/js/jquery.js"></script>
<script type="text/javascript" src="www/js/jquery-ui.js"></script>
<script type="text/javascript" src="www/js/dase/form.js"></script>
<script type="text/javascript" src="www/js/dase/item_display.js"></script>
<script type="text/javascript" src="www/js/dase/atompub.js"></script>
{/block}
{block name="title"}View Item{/block}
{block name="content"}
<div class="full" id="{$item->tagType|lower|default:'set'}">
	{if $msg}<h3 class="msg">{$msg}</h3>{/if}
	<div id="collectionAsciiId" class="pagedata">{$item->collectionAsciiId}</div>
	<div id="collSer" class="pagedata">{$item->collectionAsciiId}/{$item->entry->serialNumber}</div>
	<div id="contentHeader">

		{if $item->error}
		<h2>{$item->title}</h2>
		{else}
		<h2 class="collectionLink"><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a>
		</h2>  
		{if $item->opensearchTotal}
		<h3 class="searchEcho">Item {$item->position} of {$item->opensearchTotal} for <span class="searchEcho">{$item->query}</span></h3>
		{/if}
		{/if}

		{if $item->opensearchTotal > 1}
		<h4 class="prevNext">
			{if $item->previous}
			<a href="{$item->previous}">prev</a> |
			{else}
			<a class="nolink" href="{$item->feedLink}">prev</a> |
			{/if}
			<a href="{$item->feedLink}">up</a> |
			{if $item->next}
			<a href="{$item->next}">next</a> 
			{else}
			<a class="nolink" href="{$item->feedLink}">next</a> 
			{/if}
		</h4>
		{/if}
	</div> <!-- close contentHeader -->
	<table id="item">
		<tr>
			<td class="image">
				<img src="{$item->entry->viewitemLink}" alt="{$item->entry->_title}">
				<table>
					{foreach item=m from=$item->media}
					{if $m.label != 'thumbnail' && $m.label != 'viewitem' && $m.label != 'tiff'}
					<tr>
						<td><a href="{$m.href}"><img src="www/images/media-icons/{$m.label}.png" alt="image icon"></a></td>
						<td><a href="{$m.href}">{$m.label}</a></td>
						{if $m.height && $m.width}
						<td><a href="{$m.href}">({$m.width}x{$m.height})</a></td>
						{/if}
						<!--
						<td><a href="{$m.href}">{$m.fileSize}K</a></td>
						<td><a href="{$m.href}">{$m.type}</a></td>
						-->
					</tr>
					{/if}
					{/foreach}
				</table>
				<span class="addToCart hide">in cart</span> <a href="{$item->entry->unique}" class="addToCart hide" id="addToCart_{$item->entry->unique}">add to cart</a>
			</td>
			<td class="metadata">
				<!-- is there a better place for this?-->
				<div class="controlsContainer">
					<div id="pageReloader" class="hide"><a href="#" id="pageReloaderLink">close [X]</a></div>
					<div id="adminPageControls" class="hide">
						<a href="{$item->entry->metadataLink}" 
							id="editMetadataLink">edit</a>
						|
						<a href="{$item->entry->attributesLink}" 
							id="addMetadataLink">add metadata</a>
						|
						<a href="collection/{$item->collectionAsciiId}/item_types" 
							id="setItemTypeLink">set item type</a>
						|
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/media" 
							id="uploadMediaLink">upload media</a>
						|
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/status" 
							id="setItemStatusLink">set status (<span id="itemStatus">{$item->entry->status}</span>)</a>
						|
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}">permalink</a> 
					</div>
				</div>

				<div id="ajaxFormHolder" class="hide">
					<!-- note: javascript templates are retrieved asynchronously -->
				</div>

				<h3><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a>
					{if $item->entry->itemType.term && 'default' != $item->entry->itemType.term}
					<span id="itemType">({$item->entry->itemType.label})</span>
					{/if}
				</h3>
				<dl id="metadata" class="{$item->collectionAsciiId}">
					{foreach item=set key=att_ascii_id from=$item->entry->metadata}
					<dt>{$set.attribute_name}</dt>
					{foreach item=value from=$set.values}
					<dd>
					{if 'content' == $att_ascii_id}
					<!-- call your att 'content' and you get markdown :-) -->
					<a href="search?c={$item->collectionAsciiId}&amp;q={$att_ascii_id}:&quot;{$value.text|escape:'url'}&quot;">{$value.text|nl2br|markdown} {if $value.mod}({$value.mod}){/if}</a>
					{else}
					<a href="search?c={$item->collectionAsciiId}&amp;q={$att_ascii_id}:&quot;{$value.text|escape:'url'}&quot;">{$value.text} {if $value.mod}({$value.mod}){/if}</a>
					{/if}
					</dd>
					{/foreach}
					{/foreach}
				</dl>

				<div>
					<a href="#" class="toggle" id="toggle_adminMetadata">show/hide admin metadata</a>
				</div>
				<dl id="adminMetadata" class="{$item->collectionAsciiId} hide">
					<dt>Serial Number</dt>
					<dd>{$item->entry->serialNumber}</dd>
					<dt>Created</dt>
					<dd>{$item->entry->published}</dd>
					<dt>Updated</dt>
					<dd>{$item->entry->updated}</dd>
					{foreach item=set key=ascii_id from=$item->entry->adminMetadata}
					<dt>{$set.attribute_name}</dt>
					{foreach item=value from=$set.values}
					<dd><a href="search?c={$item->collectionAsciiId}&amp;q={$ascii_id}:{$value.text|escape:'url'}">{$value.text}</a></dd>
					{/foreach}
					{/foreach}
				</dl>

				{if $item->entry->metadataLinks|@count}
				<div id="metadataLinks">
					<h3>linked metadata</h3>
					<dl>
						{foreach item=set key=att_ascii_id from=$item->entry->metadataLinks}
						<dt>{$set.attribute_name}</dt>
						{foreach item=value from=$set.values}
						<dd><a href="{$value.url}">{$value.text} {if $value.mod}({$value.mod}){/if}</a></dd>
						{/foreach}
						{/foreach}
					</dl>
				</div>
				{/if}

				{if 'text' == $item->entry->contentType && $item->entry->content}
				<div id="itemContent">
					{$item->entry->content|nl2br|markdown}
				</div>
				{/if}

				<div class="notesForm">
					<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/comments" id="notesLink">add a user note</a> 
					<form class="hide" action="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/comments" name="notes_form" id="notesForm" method="post">
						<p><textarea rows="4" cols="50" id="note" name="note"></textarea></p>
						<p>
						<input type="submit" value="add note">
						</p>
					</form>
				</div>
				<ul id="notes"><li>&nbsp;</li></ul>
			</td>
		</tr>
	</table>
	<div id="saveToSetFormHolder">
		<form id="saveToForm" method="post" action="save">	
			<div id="saveChecked"></div>
			<input type="hidden" id="item_unique" name="item_unique" value="{$item->collectionAsciiId}/{$item->entry->serialNumber}">
		</form>
	</div>
</div> 
{/block} 
