{extends file="layout.tpl"}

{block name="content"}
<div class="full" id="browse">
	<h1>Dase Modules</h1>
	<dl>
		{foreach key=ascii item=mod from=$modules}
		<dt><a href="{$app_root}modules/{$ascii}">{$mod.name}</a></dt>
		{if $mod.description}
		<dd>
		{$mod.description}
		</dd>
		{/if}
		{/foreach}
	</dl>
</div>
{/block} 


