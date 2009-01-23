{extends file="layout.tpl"}

{block name="content"}
<div id="home">
{$home->entry->content|markdown}
</div>
{/block}

