{extends file="layout.tpl"}

{block name="content"}
{$home->entry->content|markdown}
{/block}

