{extends file="layout.tpl"}

{block name="head-links"}
{if $item->entry->editLink}
<!-- atompub -->
<link rel="edit" type="application/atom+xml" href="{$item->entry->editLink}"/>
<link rel="service" type="application/atomsvc+xml" href="{$item->entry->serviceLink}"/>
<link rel="http://daseproject.org/relation/edit" type="application/json" href="{$item->entry->jsonEditLink}"/>
{/if}
{/block}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/form.js"></script>
<script type="text/javascript" src="www/scripts/dase/item_display.js"></script>
<script type="text/javascript" src="www/scripts/dase/atompub.js"></script>
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
				<img src="{$item->entry->viewitemLink}"/>
				<table>
					{foreach item=m from=$item->media}
					{if $m.label != 'thumbnail' && $m.label != 'viewitem'}
					<tr>
						<td><a href="{$m.href}"><img src="www/images/media-icons/{$m.label}.png" alt="image icon"/></a></td>
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
				<span class="addToCart hide">in cart</span> <a href="{$item->unique}" class="addToCart hide" id="addToCart_{$item->unique}">add to cart</a>
			</td>
			<td class="metadata">
				<!-- is there a better place for this?-->
				<div class="controlsContainer">
					<div id="pageReloader" class="hide"><a href="#" id="pageReloaderLink">close [X]</a></div>
					<div id="adminPageControls" class="hide">
						<a href="{$app_root}test/demo?url={$item->entry->editLink|escape:'url'}" 
							id="editLink">poster</a>
						|
						<a href="{$item->entry->metadataLink}" 
							id="editMetadataLink">edit</a>
						|
						<!--
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/edit" 
							class="edit" id="inputFormLink">input form</a>
						|
						-->
						<a href="{$item->entry->attributesLink}" 
							id="addMetadataLink">add metadata</a>
						|
						<a href="{$item->entry->editContentLink}" 
							id="addContentLink">edit content</a>
						|
						<a href="collection/{$item->collectionAsciiId}/item_types" 
							id="setItemTypeLink">set item type</a>
						|
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/status" 
							id="setItemStatusLink">set status (<span id="itemStatus">{$item->entry->status}</span>)</a>
						{if $item->entry->parentItemTypeLinks|@count}
						{foreach item=parent key=href from=$item->entry->parentItemTypeLinks}
						|
						<a href="{$href}" class="setParentLink">link to {$parent}</a>
						{/foreach}
						{/if}

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
					<dd><a href="search?{$item->collectionAsciiId}.{$att_ascii_id}={$value.text|escape:'url'}">{$value.text}</a></dd>
					{/foreach}
					{/foreach}
				</dl>
				
				<div>
					<a href="#" class="toggle" id="toggle_adminMetadata">show/hide admin metadata</a>
				</div>
				<dl id="adminMetadata" class="{$item->collectionAsciiId} hide">
					<dt>Serial Number</dt>
					<dd>{$item->entry->serialNumber}</dd>
					<dt>Updated</dt>
					<dd>{$item->entry->updated}</dd>
					{foreach item=set key=ascii_id from=$item->entry->adminMetadata}
					<dt>{$set.attribute_name}</dt>
					{foreach item=value from=$set.values}
					<dd><a href="search?{$ascii_id}={$value.text|escape:'url'}">{$value.text}</a></dd>
					{/foreach}
					{/foreach}
				</dl>

				{if $item->content}
				<div id="itemContent">
					{$item->content|markdown}
				</div>
				{/if}

				{if $item->entry->parentLinks|@count}
				<div id="parentLinks">
					<h3>parent links</h3>
					<ul>
						{foreach item=link from=$item->entry->parentLinks}
						<li id="p_{$link.href}"><a href="{$link.href}">{$link.title}</a> <a class="hide" href="{$link.href}">[x]</a></li>
						{/foreach}
					</ul>
				</div>
				{/if}

				{if $item->entry->childFeedLinks|@count}
				<div id="childLinks">
					<h3>child links</h3>
					<ul>
						{foreach item=link from=$item->entry->childFeedLinks}
						<li><a href="{$link.href}">{$link.title}</a> ({$link.count})</li>
						{/foreach}
					</ul>
				</div>
				{/if}
				<div class="notesForm">
					<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/comments" id="notesLink">add a user note</a> 
					<form class="hide" action="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/comments" name="notes_form" id="notesForm" method="post">
						<textarea rows="4" cols="50" id="note" name="note"></textarea>
						<p>
						<input type="submit" value="add note"/>
						</p>
					</form>
				</div>
				<ul id="notes"><!-- ajax fills--></ul>
				<a
					href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}.atom?type=feed&auth=cookie"
					class="atomlogo"><img
					src="www/images/atom.jpg"/></a> 
			</td>
		</tr>
	</table>
</div> 
{if 'set' == $item->tagType}
		<div class="tagAdmin">
			<h3>Annotation <a href="#" class="modify" id="annotationToggle">add/update</a></h3>
			{if $item->entry->summary}
			<p class="annotation" id="annotationText">{$item->entry->summary}</p>
			{/if}
			<form class="hide" id="setAnnotationForm" action="{$item->self|replace:'.atom':'/annotation'}" method="post">
				<textarea name="annotation">{$item->entry->summary}</textarea>
				<input type="submit" value="save"/>
			</form>
		</div>
{/if}
{/block} 
