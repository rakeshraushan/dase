{extends file="layout.tpl"}

{block name="content"}
<div id="home">
	{if $msg}<h3 class="msg">{$msg}</h3>{/if}
{$home->entry->content|markdown}
</div>
{/block}

