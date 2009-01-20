{extends file="layout.tpl"}

{block name="head-links"}
<link rel="http://daseproject.org/relation/edit" type="application/json" href="{$person->jsonEditLink}"/>
<link rel="edit" type="application/atom+xml" href="{$person->editLink}"/>
{/block}

{block name="head"}
<script type="text/javascript" src="scripts/person_form.js"></script>
{/block}



{block name="content"}
<h1>User Information for {$person|select:'person_name'} ({$person|select:'person_eid'})</h1>
<div class="main">
	<form id="personForm" method="post" >
		<input type="hidden" name="eid" value="{$person|select:'person_eid'}"/>
		<p>
		<label for="email">Email <span class="current">[{$person|select:'person_email'}]</span></label>
		<input type="text" name="email" value="{$person|select:'person_email'}"/>
		</p>
		<p>
		<label for="phone">Phone <span class="current">[{$person|select:'person_phone'}]</span></label>
		<input type="text" name="phone" value="{$person|select:'person_phone'}"/>
		</p>
		<label for="Department">Department</label>
		{foreach item=plink from=$person->parentLinks}
		{if 'department' == $plink.item_type}
		 <span class="current">[{$plink.title|replace:'Department: ':''}]</span>
		{/if}
		{/foreach}	
		<p id="xselect_dept">
		<select name='department'>
			<option value=''>select one:</option>
			{foreach item=dept from=$depts->entries}
			<option value='${dept|select:'url'}'>${dept|select:'title'}</option>
			{/foreach}
		</select>
		<p>{$dept->title}</p>
		</p>
		<input type="submit" value="update"/>
		</p>
	</form>
</form>
</div>

<!-- javascript template for save-to pull down-->
<textarea class="javascript_template" id="select_jst">
	<select name='department'>
		<option value=''>select one:</option>
		{literal}
		{for dept in depts}
		<option value='${dept.url}'>${dept.title}</option>
		{/for}
		{/literal}
	</select>
</textarea>
<!-- end javascript template -->

{/block}

