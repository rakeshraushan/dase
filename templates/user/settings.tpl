{extends file="layout.tpl"}

{block name="content"}
<div class="full" id="settings">
	<h1>Settings for {$user->name}</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
	<h3>Search Results Display Settings</h3>
	<div id="displaySettings">
		<form action="user/{$user->eid}/display" method="post">
			<p class="current">currently: {$user->max_items} items in {$user->display} format</p>
			<span class="label">Display</span>
			<select name="max">
				<option value="15" {if 15 == $user->max_items}selected="selected"{/if}>15 items</option>
				<option value="30" {if 30 == $user->max_items || !$user->max_items}selected="selected"{/if}>30 items</option>
				<option value="50" {if 50 == $user->max_items}selected="selected"{/if}>50 items</option>
				<option value="100" {if 100 == $user->max_items}selected="selected"{/if}>100 items</option>
				<option value="200" {if 200 == $user->max_items}selected="selected"{/if}>200 items</option>
				<!--
				<option value="400" {if 400 == $user->max_items}selected="selected"{/if}>400 items</option>
				<option value="1000" {if 1000 == $user->max_items}selected="selected"{/if}>1000 items</option>
				-->
			</select>
			<select name="display">
				<option value="grid" {if "grid" == $user->display}selected="selected"{/if}> in grid layout</option>
				<option value="list" {if "list" == $user->display}selected="selected"{/if}>in list layout</option>
			</select>
			<input type="submit" value="save settings"/>
		</form>
	</div>
	{if $user->isManager()}
	<h3>Managed Collections</h3>
	<div id="managedCollections">
		<form action="user/{$user->eid}/controls" method="post">
			<p class="current">currently: {$user->cb|default:'show'} editing controls</p>
			<select name="controls">
				<option value="show" {if "show" == $user->cb}selected="selected"{/if}>show editing controls</option>
				<option value="hide" {if "hide" == $user->cb}selected="selected"{/if}>hide editing controls</option>
			</select>
			<input type="submit" value="save"/>
		</form>
		<ul>
			{foreach item=c from=$user->collections}
			{if $c.auth_level && 'none' != $c.auth_level}
			<li>{$c.collection_name} [{$c.auth_level}]</li>
			{/if}
			{/foreach}
		</ul>
	</div>
	<h3>Individual Web Service Key</h3>
	<div id="serviceKeyForm">
		<form action="user/{$user->eid}/key" method="post">
			{if $msg}<h4 class="alert">{$msg}</h4>{/if}
			<input type="password" name="key"/>
			<input type="submit" value="create service key"/>
		</form>
	</div>
	{/if}
	<a href="#" class="toggle" id="toggle_htpasswd">View HTTP Password</a>
	<span id="htpasswd" class="hide"> {$user->http_password}</span>
</div>
{/block}
