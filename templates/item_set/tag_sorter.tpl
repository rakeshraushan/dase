{extends file="layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/tag_sorter.js"></script>
{/block}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<h4 class="startSlideshow">
		<a href="tag/{$tag_feed->eid}/{$tag_feed->asciiId}">return to set</a>
	</h4>
	<h2>{$tag_feed->title} ({$tag_feed->count} items)</h2>
	<h3>{$tag_feed->subtitle}</h3>


	<table id="sorter">
		<form method="post" id="sortForm" action="tag/{$tag_feed->eid}/{$tag_feed->asciiId}/sorter">
			<tr>
				<td>List Order</td>
				<td> </td>
				<td> </td>
				<td>Title</td>
				<td>
					<input type="submit" value="update sort order"/>
				</td>
			</tr>
			{foreach key=j item=it from=$tag_feed->entries}
			<tr id="row{$it->position}">
				<td><input type="text" name="sort_item[{$it->position}]" class="{$it->position}" value="{$it->position}" size="2"/></td>
				<td>
					<a href="#" class="topper" id="sort_item[{$it->position}]"><div class="tiny">top</div><img src="www/images/tango-icons/go-up.png"/></a>
				</td>
				<td class="sortImage">
					<img alt="" src="{$it->thumbnailLink}"/>
				</td>
				<td>
					{$it->title|truncate:80:"..."}
				</td>
				<td>
				</td>
			</tr>
			{/foreach}
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>
					<input type="submit" value="update sort order"/>
				</td>
			</tr>
		</form>
	</table>

	<div id="tagEid" class="pagedata">{$tag_feed->eid}</div>
	<div id="tagName" class="pagedata">{$tag_feed->title}</div>
	<div id="tagAsciiId" class="pagedata">{$tag_feed->asciiId}</div>
	<div class="spacer"></div>
</div>
{/block}
