{extends file="layout.tpl"}
{block name="head"}
<script type="text/javascript" src="www/scripts/dase/form.js"></script>
{/block}
{block name="title"}View Item{/block}
{block name="content"}
<div class="full" id="{$item->tagType|lower}">
	<div id="collectionAsciiId" class="pagedata">{$item->collectionAsciiId}</div>
	<div id="collSer" class="pagedata">{$item->collectionAsciiId}/{$item->serialNumber}</div>
	<div id="contentHeader">
		<h1><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a> : <span class="searchEcho">{$item->query}</span></h1>
		{if $item->opensearchTotal > 1}
		<h4>
			<a href="{$item->previous}">prev</a> |
			<a href="{$item->feedLink}">up</a> |
			<a href="{$item->next}">next</a> 
		</h4>
		{/if}
	</div> <!-- close contentHeader -->
	<div class="controlsContainer">
		<div id="adminPageControls" class="hide">
			<!-- is there a better place for this?-->
			<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}/templates" class="pagedata" id="jsTemplatesUrl"></a>
			<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}/edit" class="edit" id="editLink">edit</a>
			|
			<!--
			<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}/edit" class="edit" id="inputFormLink">input form</a>
			|
			-->
			<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}/edit" class="edit" id="addMetadataLink">add metadata</a>
		</div>
	</div>
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
						<td><a href="{$m.href}">{if $m.height && $m.width}({$m.width}x{$m.height}){/if}</a></td>
						<!--
						<td><a href="{$m.href}">{$m.fileSize}K</a></td>
						<td><a href="{$m.href}">{$m.type}</a></td>
						-->
					</tr>
					{/if}
					{/foreach}
				</table>
			</td>
			<td class="metadata">

				<!-- adding metadata section-->
				<div id="addMetadata" class="hide">
					<!-- note: javascript templates are retrieved asynchronously -->
				</div>
				<!-- end adding metadata section-->

				<h3><a href="collection/{$item->collectionAsciiId}">{$item->collection}</a></h3>
				<dl id="metadata" class="{$item->collectionAsciiId}">
					{foreach item=set key=ascii_id from=$item->metadata}
					<dt>{$set.attribute_name}</dt>
					{foreach item=value from=$set.values}
					<dd><a href="search?{$item->collectionAsciiId}.{$ascii_id}={$value|escape:'url'}">{$value}</a></dd>
					{/foreach}
					{/foreach}
				</dl>
				<div id="metadata_form_div" class="hide"></div>


				<div id="itemLinks">
					<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}/micro">micro</a> 
					|
					<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}.atom?auth=cookie">atom</a> 
					|
					<a href="item/{$item->collectionAsciiId}/{$item->serialNumber}/notes" id="notesLink">user notes</a> 
				</div>
				<div class="spacer"></div>
				<div id="notesForm" class="hide">
					<form action="item/{$item->collectionAsciiId}/{$item->serialNumber}/notes" name="notes_form" id="notesForm" method="post">
						<textarea rows="7" cols="60" id="note" name="note"></textarea>
						<p>
						<input type="submit" value="add note"/>
						</p>
					</form>
				</div>
				<ul id="notes">
					{foreach item=note from=$item->notes}
					<li>{$note.text}</li>
					{/foreach}
				</ul>
			</td>
		</tr>
	</table>
	<div id="adminStatusControls" class="item/{$item->collectionAsciiId}/{$item->serialNumber}/status"></div>

	{if $item->editLink}
	<!-- this is an atompub thing -->
	<div><a class="hide" id="editLink" href="{$item->editLink}">edit item</a></div>
	{/if}
</div> <!-- close content -->
{/block} 
