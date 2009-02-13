{if $props->entries}
	{if 1 == $props->count}
		{assign var=sorted_feed value=$props|sortby:'proposal_chair_rank'}
		{foreach key=i item=proposal from=$sorted_feed->entries}
			<li class="{$proposal->proposal_chair_rank.edit}"><span>{$proposal->proposal_chair_rank.text}. <strong>{$proposal->proposal_name.text}</strong> (<a href="proposal/{$proposal->serialNumber}/preview">preview</a>) submitted by: {$proposal->getParentLinkTitleByItemType('person')}	{$proposal->proposal_submitted.text|date_format:"%a, %b %e %Y at %l:%M%p"} </span></li> 
		{/foreach}
	</ul>
	{else}

	<ul id="sortable">
		{assign var=sorted_feed value=$props|sortby:'proposal_chair_rank'}
		{foreach key=i item=proposal from=$sorted_feed->entries}
			<li class="{$proposal->proposal_chair_rank.edit}"><span>{$proposal->proposal_chair_rank.text}. <strong>{$proposal->proposal_name.text}</strong> (<a href="proposal/{$proposal->serialNumber}/preview">preview</a>) submitted by: {$proposal->getParentLinkTitleByItemType('person')}	{$proposal->proposal_submitted.text|date_format:"%a, %b %e %Y at %l:%M%p"} </span></li> 
		{/foreach}
	</ul>
		<input type='submit' id="update_sort" value='update order'>
	{/if}
{/if}

