{extends file="layout.tpl"}

{block name="content"}
<div class="main">
	<table class="listing">
		<tr>
			<th>name</th>
			<th>eid</th>
			<th>phone</th>
			<th>email</th>
		</tr>
		{foreach item=person from=$persons->entries}
		<tr>
			<td>{$person|select:'person_name'}</td>
			<td>{$person|select:'person_eid'}</td>
			<td>{$person|select:'person_phone'}</td>
			<td>{$person|select:'person_email'}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}

