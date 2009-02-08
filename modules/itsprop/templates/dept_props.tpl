{if $props->entries}
<table id="sorter" class="{$sort_token}">
	{if 1 == $props->count}
	{foreach key=i item=proposal from=$props->entries}
	<tr id="row{$proposal->proposal_chair_rank.id}">
		<td class="title" colspan="3">
			{$proposal->proposal_chair_rank.id}
			<span class="title">{$proposal->proposal_name.text}</span>
			<span class="miniLink">
				(<a href="proposal/{$proposal->serialNumber}/preview">preview</a>)
				submitted by: {$proposal->getParentLinkTitleByItemType('person')}
				{$proposal->proposal_submitted.text|date_format:"%a, %b %e %Y at %l:%M%p"}
			</span>
			<p><a href="ss" class="toggle" id="toggle_prop{$i+1}">add/edit comment</a></p>
			<form method="post" class="hide" id="prop{$i+1}" >
				<textarea class="comment" name="comment">{$proposal->proposal_chair_comments.text}</textarea>
				<p>
				<input type="submit" value="update">
				</p>
			</form>
		</td>
	</tr>
	{/foreach}
	{else}
	<tr>
		<td colspan="3" class="updateButton">
			<input type="submit" id="button1" value="update sort order">
			<span id="updating_one" class="hide updating">updating sort order...</span>
		</td>
	</tr>
	<tr class="instruction">
		<td colspan="3">
			Enter positions and <strong>update sort order</strong> OR move single item to the top with up arrow.
		</td>
	</tr>
	{foreach key=i item=proposal from=$props->entries}
	<tr id="row{$proposal->proposal_chair_rank.id}">
		<td class="sort">
			<input action="{$proposal->proposal_chair_rank.edit}" type="text" id="{$proposal->proposal_chair_rank.id}" name="sort_item[{$proposal->proposal_chair_rank.text}]" class="{$proposal->proposal_chair_rank.text}" value="{$proposal->proposal_chair_rank.text}" size="2"/>
		</td>
		<td class="topper">
			<a href="{$proposal->proposal_chair_rank.edit}" class="topper" id="topper{$proposal->proposal_chair_rank.id}"><div class="tiny">top</div><img src="{$app_root}www/images/tango-icons/go-up-small.png"></a>
		</td>
		<td class="title">
			{$proposal->proposal_chair_rank.id}
			<span class="title">{$proposal->proposal_name.text}</span>
			<span class="miniLink">
				(<a href="proposal/{$proposal->serialNumber}/preview">preview</a>)
				submitted by: {$proposal->getParentLinkTitleByItemType('person')}
				{$proposal->proposal_submitted.text|date_format:"%a, %b %e %Y at %l:%M%p"}
			</span>
			<p><a href="ss" class="toggle" id="toggle_prop{$i+1}">add/edit comment</a></p>
			<form method="post" class="hide" id="prop{$i+1}" >
				<textarea class="comment" name="comment">{$proposal->proposal_chair_comments.text}</textarea>
				<p>
				<input type="submit" value="update">
				</p>
			</form>
		</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="3" class="updateButton">
			<input type="submit" id="button2" value="update sort order">
			<span id="updating_two" class="hide updating">updating sort order...</span>
		</td>
	</tr>
	{/if}
</table>
{/if}

