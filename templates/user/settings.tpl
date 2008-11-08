{extends file="layout.tpl"}

{block name="content"}
<div class="full" id="settings">
	<div id="contentHeader">
		<h1>Settings for {$user->name}</h1>
	</div>
	<div id="userSettings">
		<!--
		<h2>{$user->ppd}</h2>
		-->
		<a href="#" class="toggle" id="toggle_htpasswd">View HTTP Password</a>
		<span id="htpasswd" class="hide"> {$user->http_password}</span>
		<div id="serviceKeyForm">
			<form action="user/{$user->eid}/key" method="post">
				{if $msg}<h3 class="alert">{$msg}</h3>{/if}
				<input type="password" name="key"/>
				<input type="submit" value="create service key"/>
			</form>
		</div>
	</div>
</div>
{/block}
