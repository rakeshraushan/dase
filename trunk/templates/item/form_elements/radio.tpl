{foreach item=val from=$a->form_values}
<p>
<input type="radio" value="{$val}"  name="{$a->ascii_id}"> {$val}
</p>
{/foreach}

