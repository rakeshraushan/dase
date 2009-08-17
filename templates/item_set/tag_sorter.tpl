{extends file="layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/tag_sorter.js"></script>
{/block}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<div class="pageControls">
		<h4>
			<a href="tag/{$tag_feed->eid}/{$tag_feed->asciiId}">return to set</a>
		</h4>
	</div>
	<h2>{$tag_feed->title} ({$tag_feed->count} items)</h2>
	<h3>{$tag_feed->subtitle}</h3>


	<table id="sorter">
		<form method="post" id="sortForm" action="tag/{$tag_feed->eid}/{$tag_feed->asciiId}/sorter">
			<tr>
				<td></td>
				<td class="position">
					<input type="submit" value="update sort order">
				</td>
				<td> </td>
				<td>Title</td>
			</tr>
			{foreach key=j item=it from=$tag_feed->entries}
			<tr id="row{$it->position}">
				<td class="arrow">
					<a href="#" class="topper" id="{$it->tagItemId}"><div class="tiny">top</div><img src="www/images/tango-icons/go-up.png"></a>
				</td>
				<td class="position"><input type="text" id="input_{$it->tagItemId}" name="sort_item[{$it->tagItemId}]" class="{$it->position}" value="{$it->position}" size="2"/></td>
				<td class="sortImage">
					<img alt="" src="{$it->thumbnailLink}"/>
				</td>
				<td class="title">
					{$it->_title}
				</td>
			</tr>
			{/foreach}
			<tr>
				<td></td>
				<td class="position">
					<input type="submit" value="update sort order">
				</td>
				<td></td>
				<td></td>
			</tr>
		</form>
	</table>

	<div id="tagEid" class="pagedata">{$tag_feed->eid}</div>
	<div id="tagName" class="pagedata">{$tag_feed->title}</div>
	<div id="tagAsciiId" class="pagedata">{$tag_feed->asciiId}</div>
	<div class="spacer"></div>
</div>
{/block}
