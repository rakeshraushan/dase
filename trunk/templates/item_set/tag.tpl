{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="content"}
<div class="full" id="browse">
	<div id="msg" class="alert hide"></div>
	<form id="saveToForm" method="post" action="save">	
		<table id="itemSet">
			{assign var=startIndex value=$items->startIndex}
			{include file='item_set/common.tpl' start=$startIndex}
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<div id="saveChecked"></div>
	</form>
	<!--
	left over from xslt
	<form method="post" action="user/{$user/eid}/tag/{$tag_ascii_id}/remove_items">
		<table id="itemSet">
			<xsl:apply-templates select="$items/atom:entry" mode="items"/>
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<input type="submit" name="remove_checked" id="removeFromSet" value="remove checked items from set"/>
	</form>
	-->
	<div class="spacer"></div>
</div>
{/block}
