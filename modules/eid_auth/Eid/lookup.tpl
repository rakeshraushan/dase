<div class="content list" id="browse">
<a id="content" name="content"></a>

{if $msg}
<div class="alert">{$msg}</div>
{/if}

<h1>UT Austin Public Directory Lookup</h1>

<form id="login" action="eid/lookup" method="get">
<p>
<label for="query">query</label>
<input type="text" name="query">
</p>
<p>
<label for="type_select">select query type</label>
<select name="type">
<option value="sn">last name</option>
<option value="uid">eid</option>
</select>
</p>
<p>
<input type="submit" value="go">
</p>
</form>

{if $person_array}
{foreach key=eid item=person from=$person_array}
<dl>
<dt>name:</dt>
<dd>{$person.name}</dd>
<dt>eid:</dt>
<dd>{$person.eid}</dd>
<dt>email:</dt>
<dd>{$person.email|default:'none listed'}</dd>
</dl>
<div class="spacer"></div>
{/foreach}
{/if}


</div> 
