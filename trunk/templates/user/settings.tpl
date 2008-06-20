{extends file="admin/layout.tpl"}

{block name="content"}
<div class="full" id="settings">
	<div id="contentHeader">
		<h1>Settings for {$user->name}</h1>
		<h2>{$user->ppd}</h2>
		<h2>REST key: {$user->http_password}</h2>
	</div>
</div>
{/block}
