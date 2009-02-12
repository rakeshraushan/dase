{extends file="layout.tpl"}

{block name="content"}
<div id="home">
{$home->content|markdown}
{if $request->is_superuser}
<a href="home_form">edit this page</a>
{/if}
</div>
{/block}

