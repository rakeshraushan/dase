{extends file="layout.tpl"}

{block name="content"}
<div class="main">
	<table class="listing">
		<tr>
			<th>ID</th>
			<th>name</th>
			<th>phone</th>
			<th>FAX</th>
			<th>email</th>
			<th>staff contact</th>
			<th>chair</th>
			<th>chair title</th>
			<th>chair email</th>
		</tr>
		{foreach item=dept from=$depts->entries}
		<tr>
			<td>{$dept|select:'dept_id'}</td>
			<td>{$dept|select:'dept_name'}</td>
			<td>{$dept|select:'dept_phone'}</td>
			<td>{$dept|select:'dept_fax'}</td>
			<td>{$dept|select:'dept_email'}</td>
			<td>{$dept|select:'dept_staff_contact'}</td>
			<td>{$dept|select:'dept_chair'}</td>
			<td>{$dept|select:'dept_chair_title'}</td>
			<td>{$dept|select:'dept_chair_email'}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}

