{extends file="layout.tpl"}

{block name="content"}
<div class="main">
	<table class="listing">
		<tr>
			<th>name</th>
			<th>eid</th>
			<th>phone</th>
			<th>email</th>
			<th>department</th>
			<th></th>
		</tr>
		{foreach item=person from=$persons->entries}
		<tr>
			<td>{$person->person_name.text}</td>
			<td>{$person->person_eid.text}</td>
			<td>{$person->person_phone.text}</td>
			<td>{$person->person_email.text}</td>
			<td>{$person->department}</td>
			<td><a href="person/{$person->person_eid.text}" class="modify">modify</a></td>
		</tr>
		{/foreach}
	</table>
	<form method="post">
		<p>
		<input type="submit" value="add user by eid">
		<input type="text" name="eid">
		</p>
	</form>
</div>
{/block}

