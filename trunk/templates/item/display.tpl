{extends file="layout.tpl"}
{block name="head"}
<script type="text/javascript" src="www/scripts/dase/form.js"></script>
<script type="text/javascript" src="www/scripts/dase/item_display.js"></script>
{/block}
{block name="title"}View Item{/block}
{block name="content"}
<div class="full" id="{$item->tagType|lower|default:'set'}">
	<div id="collectionAsciiId" class="pagedata">{$item->collectionAsciiId}</div>
	<div id="collSer" class="pagedata">{$item->collectionAsciiId}/{$item->entry->serialNumber}</div>
	<div id="contentHeader">

		{if $item->error}
		<h2>{$item->title}</h2>
		{else}
		<h2 class="collectionLink"><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a></h2>  
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
				<img src="{$item->viewitemLink}"/>
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
				<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/templates" class="pagedata" id="jsTemplatesUrl"></a>
				<div class="controlsContainer">
					<div id="pageReloader" class="hide"><a href="#" id="pageReloaderLink">close [X]</a></div>
					<div id="adminPageControls" class="hide">
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/metadata" class="edit" id="editLink">edit</a>
						|
						<!--
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/edit" class="edit" id="inputFormLink">input form</a>
						|
						-->
						<a href="collection/{$item->collectionAsciiId}/attributes" class="edit" id="addMetadataLink">add metadata</a>
						|
						<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/content" class="edit" id="addContentLink">add/edit textual content</a>
					</div>
				</div>

				<div id="ajaxFormHolder" class="hide">
					<!-- note: javascript templates are retrieved asynchronously -->
				</div>

				<h3><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a></h3>
				<dl id="metadata" class="{$item->collectionAsciiId}">
					{foreach item=set key=ascii_id from=$item->metadata}
					{if 'yes' eq $set.public}
					<dt>{$set.attribute_name}</dt>
					{foreach item=value from=$set.values}
					<dd><a href="search?{$item->collectionAsciiId}.{$ascii_id}={$value|escape:'url'}">{$value}</a></dd>
					{/foreach}
					{/if}
					{/foreach}
				</dl>
				<div>
					<a href="#" class="toggle" id="toggle_adminMetadata">show/hide admin metadata</a>
				</div>
				<dl id="adminMetadata" class="{$item->collectionAsciiId} hide">
					{foreach item=set key=ascii_id from=$item->adminMetadata}
					<dt>{$set.attribute_name}</dt>
					{foreach item=value from=$set.values}
					<dd><a href="search?{$item->collectionAsciiId}.{$ascii_id}={$value|escape:'url'}">{$value}</a></dd>
					{/foreach}
					{/foreach}
				</dl>
				{if $item->content}
				<div id="itemContent">
					{$item->content|markdown}
				</div>
				{/if}

				<div id="itemLinks">
					<a href="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/comments" id="notesLink">add a user note</a> 
				</div>

				<div class="spacer"></div>
				<div id="notesForm" class="hide">
					<form action="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/comments" name="notes_form" id="notesForm" method="post">
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
	<div id="adminStatusControls" class="item/{$item->collectionAsciiId}/{$item->entry->serialNumber}/status"></div>

	{if $item->editLink}
	<!-- this is an atompub thing and it will supply the action for the edit metadata form-->
	<div><a class="hide" id="editLink" href="{$item->editLink}">edit item</a></div>
	{/if}
</div> 
{if 'set' == $item->tagType}
		<div class="tagAdmin">
			<h4>annotate slide</h4>
			{if $item->entry->summary}
			<p class="annotation">{$item->entry->summary}</p>
			{/if}
			<form id="setAnnotationForm" action="{$item->self|replace:'.atom':'/annotation'}" method="post">
				<textarea name="annotation"></textarea>
				<input type="submit" value="save"/>
			</form>
		</div>
{/if}
{/block} 
