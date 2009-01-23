{extends file="layout.tpl"}

{block name="head-links"}
<link rel="http://daseproject.org/relation/edit" type="application/json" href="{$person->jsonEditLink}"/>
<link rel="edit" type="application/atom+xml" href="{$person->editLink}"/>
{/block}

{block name="content"}
<h1>User Information for {$person->person_name} ({$person->person_eid})</h1>
<div class="main">
	<form id="personForm" method="post" >
		<input type="hidden" name="eid" value="{$person->serial_number}"/>
		<p>
		<label for="name">Name</label>
		<input type="text" name="name" value="{$person->person_name}"/>
		</p>
		<p>
		<label for="email">Email</label>
		<input type="text" name="email" value="{$person->person_email}"/>
		</p>
		<p>
		<label for="phone">Phone</label>
		<input type="text" name="phone" value="{$person->person_phone}"/>
		</p>
		<p>
		<label for="Department">Department</label>
		{foreach item=plink from=$person->parentLinks}
		{if 'department' == $plink.item_type}
		{assign var=dept_url value=$plink.href}
		{assign var=dept_title value=$plink.title}
		{/if}
		{/foreach}	
		<select name='department'>
			<option value=''>select one:</option>
			{foreach key=url item=dept_name from=$depts}
			<option {if $dept_url == $url}selected="selected"{/if} value='{$url}'>{$dept_name}</option>
			{/foreach}
		</select>
		</p>
		<p>
		<input type="submit" value="update"/>
		<input type="submit" name="refresh" value="refresh from directory"/>
		</p>
	</form>
	<dl class="current">
		<dt>eid</dt> <dd> {$person->person_eid|default:'--'}</dd>
		<dt>name</dt> <dd> {$person->person_name|default:'--'}</dd>
		<dt>email</dt> <dd> {$person->person_email|default:'--'}</dd>
		<dt>phone</dt> <dd> {$person->person_phone|default:'--'}</dd>
		<dt>dept</dt> <dd> {$dept_title|default:'--'}</dd>
	</dl>
</div>
{/block}

