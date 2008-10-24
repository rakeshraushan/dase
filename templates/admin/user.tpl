{extends file="admin/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	<h1>Dase User Information</h1>
	<h2>{$user->name} ({$user->eid})</h2>
</div>
<div id="userInfo">
	<h3>sets</h3>
	<ul>
		{foreach item=tag from=$tags}
		<li>{$tag.name} ({$tag.count} items)</li>
		{/foreach}
	</ul>
	<h3>collections</h3>
	<ul>
		{foreach item=c from=$collections}
		{if $c.auth_level}
		<li>{$c.collection_name} ({$c.auth_level})</li>
		{/if}
		{/foreach}
	</ul>
</div>
{/block} 


