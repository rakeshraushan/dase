{extends file="layout.tpl"}
{block name="title"}DASe: Collections List{/block} 

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h5>Search selected collection(s):</h5> 
	<form method="get" action="search">
		<div>
			<input type="text" name="q" size="30"/>
			<input type="submit" value="Search" class="button"/>
		</div>
		<ul id="collectionList" class="multicheck">
			{foreach item=ent from=$collections->entry}
			<li id="{$ent->content}">
			<input name="c" value="{$ent->content}" checked="checked" type="checkbox"/>
			<a href="collection/{$ent->content}" class="checkedCollection">{$ent->title|escape}</a>
			<span class="tally"></span>
			</li>
			{/foreach}
			<li id="specialAccessLabel" class="hide"><h4>Special Access Collections</h4></li>
		</ul>
		<div>
			<a href="" id="checkall">check/uncheck all</a>
		</div>
	</form>
</div>
{/block}
