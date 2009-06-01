<input type="text" name="{$a->ascii_id}" class="autofill_target">
<br>
<select id="autofill_select_{$a->ascii_id}">
<option value="">select one:</option>
{foreach item=val from=$a->form_values}
<option value="{$val}">{$val}</option>
{/foreach}
</select>


