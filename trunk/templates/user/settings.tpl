{extends file="layout.tpl"}

{block name="content"}
<div class="full" id="settings">
	<div id="contentHeader">
		<h1>Settings for {$user->name}</h1>
		<h2>{$user->ppd}</h2>
	</div>
	<h3>Managed Collections</h3>
	<ul id="managedCollections">
		{foreach item=c from=$user->collections}
		<li>
		{$c.collection_name} ({$c.auth_level})
		<a href="user/{$user->eid}/collection/{$c.ascii_id}/auth/read">read</a> |
		<a href="user/{$user->eid}/collection/{$c.ascii_id}/auth/write">write</a> |
		<a href="user/{$user->eid}/collection/{$c.ascii_id}/auth/admin">admin</a> 
		</li>
		{/foreach}
	</ul>
</div>
{/block}
