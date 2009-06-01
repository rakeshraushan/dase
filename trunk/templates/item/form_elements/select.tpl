<select name="{$a->ascii_id}">
<option value="">select one:</option>
{foreach item=val from=$a->form_values}
<option value="{$val}">{$val}</option>
{/foreach}
</select>


