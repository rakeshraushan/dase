{extends file="layout.tpl"}
{block name="title"}DASe: Public User Sets{/block} 

{block name="head"}
{/block}

{block name="content"}
<div class="full" id="setlist">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Public User Sets</h1>
	<table class="userSets"><tr>
			<td>
				<h2>{$sets->title}</h2>
				<ul>
					{foreach item=set from=$sets->entries}
					<li>
					<a href="{$set->alternateLink}">{$set->title|escape} ({$set->itemCount} items)</a>
					</li>
					{/foreach}
				</ul>
			</td>
			<td class="filters">
				<h2>filter by course</h2>
				<ul>
					<li>
					<a href="sets">View All (no filter)</a>
					</li>
					{foreach item=course from=$courses->all}
					<li>
					<a href="sets?category={literal}{http://daseproject.org/category/utexas/courses}{/literal}{$course.term}">{$course.label}</a>
					</li>
					{/foreach}
				</ul>
			</td>
	</tr></table>
</div>
{/block}
