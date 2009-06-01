
<form action="{$item_url}" method="post">
{foreach key=ascii item=a from=$atts}
<p>
<label for="{$asciii}">{$a->attribute_name}</label>
{assign var=file value=$a->html_input_type}
{include file="item/form_elements/$file.tpl"}
</p>
{/foreach}
<input type="submit" value="Add Metadata">
</form>
