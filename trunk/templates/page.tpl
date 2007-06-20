{include file="head.tpl"}

<body>

{include file="boilerplate.tpl"}

{include file="banner.tpl"}

{if $cb}
{include file="admin_sidebar.tpl"}
{else}
{include file="sidebar.tpl"}
{/if}

{include file="content/$content.tpl"}

{include file="footer.tpl"}

</body>
</html>

