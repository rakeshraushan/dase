{extends file="layout.tpl"}

{block name="head-links"}
<link rel="http://daseproject.org/relation/edit" type="application/json" href="{$person->jsonEditLink}"/>
<link rel="edit" type="application/atom+xml" href="{$person->editLink}"/>
{/block}

{block name="content"}
<h1>User Information for {$person|select:'person_name'} ({$person|select:'person_eid'})</h1>
<div class="main">
	<form id="personForm" method="post" >
		<input type="hidden" name="eid" value="{$person|select:'person_eid'}"/>
		<p>
		<label for="email">Email</label>
		<input type="text" name="email" value="{$person|select:'person_email'}"/>
		</p>
		<p>
		<label for="phone">Phone</label>
		<input type="text" name="phone" value="{$person|select:'person_phone'}"/>
		</p>
		<label for="Department">Department</label>
		{foreach item=plink from=$person->parentLinks}
		{if 'department' == $plink.item_type}
		{assign var=dept_url value=$plink.href}
		{/if}
		{/foreach}	
		<p>
		<select name='department'>
			<option value=''>select one:</option>
			{foreach key=url item=dept_name from=$depts}
			<option {if $dept_url == $url}selected="selected"{/if} value='{$url}'>{$dept_name}</option>
			{/foreach}
		</select>
		</p>
		<input type="submit" value="update"/>
	</form>
</div>
{/block}

