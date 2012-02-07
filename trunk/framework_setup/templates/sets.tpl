{extends file="layout.tpl"}

{block name="content"}
<div class="controls">
	<a href="set/form">create a set</a>
</div>
<h1>Sets</h1>
<ul class="sets">
	{foreach item=set from=$sets}
	<li><a href="set/{$set->name}">{$set->title}</a></li>
	{/foreach}
</ul>
{/block}