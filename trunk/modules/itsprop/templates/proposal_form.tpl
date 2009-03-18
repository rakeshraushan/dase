{extends file="layout.tpl"}

{block name="content"}
<h1>New Grant Proposal Form</h1>
<div class="main">
	<form id="proposalShortForm" class="shortForm" method="post" >
		<input type="hidden" name="eid" value="{$person->serial_number.text}"/>
		<p>
		<label for="name">Proposer Name</label>
		<input type="text" name="name" value="{$person->person_name.text}" disabled="disabled"/>
		</p>
		<p>
		<label for="email">Proposal Title</label>
		<input type="text" name="proposal_name" />
		</p>
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
		<label for="proposal_project_type">Project Type</label>
		<select name="proposal_project_type">
			<option value="">Select category best describing your proposal ...</option>
			<option value="">---------------------</option>
			<option value="Course materials development">Course materials development</option>
			<option value="Computer labs and/or servers">Computer labs and/or servers</option>
			<option value="Technology classroom">Technology classroom</option>
			<option value="Network">Network</option>
		</select>
		</p>
		<p>
		<input type="submit" value="start new proposal"/>
		</p>
	</form>
</div>
{/block}

