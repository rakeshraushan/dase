{include file="header.tpl"}

<script type="text/javascript">
</script>

<div id="center-content">

<ul class="thumbnail">
{if $items}
{foreach from=$items item=item}
<div style="height:188px;float:left;"><li>
<div style="height:112px;width: 150px;">
<a href="viewitem/{$item.serial_number}"><img src="http://dase.laits.utexas.edu/media/friesen_collection/thumbnail/{$item.thumbnail}"/></a>
</div>
<div class="thumb-caption">
{$item.title}
</div>
</li></div>
{/foreach}
{else}
<h2>No Items Found</h2>
{/if}
</ul>

</div> <!-- end class="content" -->
<div class="spacer-help" align="right">
<form class="searchForm" method="get" action="search">
<input type="text" name="query" size="12" />
<input type="submit" value="Search" class="button"/>
<span id="status"></span>
</form>
or 
<form class="searchForm" method="get" id="kwform" action="search">
<select name="query">
<option>Select Topic by Keyword</option>
{foreach item=kw key=hash from=$keywords}
<option value="{$kw}">{$kw}</option>
{/foreach}
</select>
<input type="submit" value="go"/>
</form>
</div>

</body>
</html>
