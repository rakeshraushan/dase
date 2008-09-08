{extends file="layout.tpl"}

{block name="content"}
<div class="full" id="settings">
	<div id="contentHeader">
		<h1>Settings for {$user->name}</h1>
		<!--
		<h2>{$user->ppd}</h2>
		-->
		<a href="#" id="htpasswdToggle">View DASe Services Password</a>
		<span id="htpasswd" class="hide"> {$user->http_password}</span>
	</div>
</div>
{/block}
