{include file="head.tpl"}

<body>
<div id="skipnav"><a href="#content" title="Skip to main content">Skip to main content</a></div>

<noscript>
<h1 class="alert">The optimal DASe experience requires Javascript!</h1>
</noscript>

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

