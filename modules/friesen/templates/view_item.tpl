{include file="header.tpl"}

<div class="page-right">
<h3>{$item.title}</h3>
<p>{$item.text}</p>
</div>
<div class="page-left">
<img src="http://dase.laits.utexas.edu/media/friesen_collection/viewitem/{$item.viewitem}" width="" height=""/>
<div class="caption">{$item.caption}</div>
</div>
<div class="spacer-help">
keywords:
<div class="keywords">
{foreach from=$item.keywords item=kw name=k}
<a href="search?query={$kw}">{$kw}</a>
{if $smarty.foreach.k.last}{else}, {/if}
{foreachelse}
{/foreach}
</div>
</div>
</div>
</body>
</html>
