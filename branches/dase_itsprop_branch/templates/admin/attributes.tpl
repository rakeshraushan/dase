{extends file="admin/layout.tpl"}

{block name="content"}
<div id="contentHeader">
	<h1>Dase Admin Attributes</h1>
	<ul>
		{foreach item=att from=$atts}
		<li>{$att->attribute_name} ({$att->ascii_id})</li>
		{/foreach}
	</ul>
</div>
{/block} 


