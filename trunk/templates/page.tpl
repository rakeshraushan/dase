{include file="head.tpl"}

<body>

<!--[if IE]>
{literal}
<style type="text/css">
ul#menuNav li {
display: inline;
}
div.ieAlert {
margin: 20px 0 20px 0;
		font-size: 1.2em;
color: #b33;
display: block;
}
</style>
{/literal}
<![endif]-->
<a href="#content" title="Skip to main content" class="skipnav">Skip to main content</a>

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

