{extends file="admin/layout.tpl"}

{block name="head"}
<script type="text/javascript">
</script>
{/block}

{block name="content"}
<div id="contentHeader">
	<h1>Dase Users</h1>
	<form id="namesearch" action="admin/users" method="get">
		<input type="text" name="q"/>
		<input type="submit" value="search"/>
	</form>
</div>
<h2 id="userCount">{$users|@count} users found</h2>
<ul id="userList">
	{foreach item=user from=$users}
	<li><a href="admin/user/{$user->eid}"><strong>{$user->eid}</strong>: {$user->name}</a></li>
	{/foreach}
</ul>
{/block} 


