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
			{foreach item=c from=$collections->entries}
			<li id="{$c->ascii_id}">
			<input name="c" value="{$c->ascii_id}" checked="checked" type="checkbox"/>
			<a href="collection/{$c->ascii_id}" class="checkedCollection">{$c->name|escape}</a>
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
