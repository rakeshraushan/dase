{extends file="layout.tpl"}

{block name="content"}
<div class="main">
	<table class="listing">
		<tr>
			<!--
			<th>ID</th>
			-->
			<th>name</th>
			<!--
			<th>phone</th>
			<th>FAX</th>
			<th>email</th>
			<th>staff contact</th>
			<th>chair title</th>
			-->
			<th>chair</th>
			<th>chair email</th>
			<th></th>
		</tr>
		{foreach item=dept from=$depts->entries}
		<tr class="display_{$dept->dept_display.text}">
			<!--
			<td>{$dept->dept_id.text}</td>
			-->
			<td>{$dept->dept_name.text}</td>
			<!--
			<td>{$dept->dept_phone.text}</td>
			<td>{$dept->dept_fax.text}</td>
			<td>{$dept->dept_email.text}</td>
			<td>{$dept->dept_staff_contact.text}</td>
			<td>{$dept->dept_chair_title.text}</td>
			-->
			<td>{$dept->dept_chair.text}</td>
			<td>{$dept->dept_chair_email.text}</td>
			<td><a href="department/{$dept->dept_id.text}" class="modify">modify</a></td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}

