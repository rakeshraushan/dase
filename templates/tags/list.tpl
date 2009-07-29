{extends file="layout.tpl"}
{block name="title"}DASe: Public User Sets{/block} 

{block name="head"}
{/block}

{block name="content"}
<div class="full" id="setlist">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Public User Sets</h1>
	<table class="userSets"><tr>
			<td class="setList">
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
					{if $course.term != $course.label}
					<li>
					<a href="sets?category={literal}{http://daseproject.org/category/utexas/courses}{/literal}{$course.term}">{$course.label}</a>
					</li>
					{/if}
					{/foreach}
				</ul>
				<h3>Faculty members can assign a set to a particular course through <a href="http://courses.utexas.edu" class="outbound">Blackboard</a></h3>
			</td>
	</tr></table>
</div>
{/block}
