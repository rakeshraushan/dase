{extends file="layout.tpl"}

{block name="title"}Item Set{/block}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/annotate_slides.js"></script>
<script type="text/javascript" src="www/scripts/dase/item_set_display.js"></script>
<script type="text/javascript" src="www/scripts/dase/slideshow.js"></script>
{/block}

{block name="content"}
<div class="full" id="{$items->tagType|lower|default:'set'}">
	<div class="pageControls">
		<h4>
			<a href="#" id="startSlideshow">view slideshow</a> |
			<a href="{$items->link}">return to set</a>
		</h4>
	</div>
	<h1>{$items->title}</h1>
	<h4>set slideshow background color</h4>
	<form action="tag/{$items->eid}/{$items->asciiId}/background" method="post">
		<p>
		<select name="background" class="{$items->background}">
			<option>select a color:</option>
			<option class="black" {if "black" == $items->background}selected="selected"{/if}>black</option>
			<option class="white" {if "white" == $items->background}selected="selected"{/if}>white</option>
		</select>
		<input type="hidden" name="display" value="{$display}">
		<input type="submit" value="save">
		</p>
	</form>
	<table id="annotate">
		{foreach item=it from=$items->entries}
		<tr>
			<td class="annotation">
				<h3>Annotation</h3>
				<p class="annotation" id="{$it->tagItemId}_annotation">{$it->summary}</p>
				<form class="hide" id="{$it->tagItemId}_form" action="{$it->editAnnotationLink}" method="post">
					<p><textarea name="annotation">{$it->summary}</textarea></p>
					<input type="submit" value="save">
				</form>
				<a href="#" id="{$it->tagItemId}" class="toggleForm">[{if $it->summary}edit{else}add annotation{/if}]</a>
			</td>
			<th>
				<img src="data:image/png;base64,{$it->viewitemBase64}"/>
				<h3>{$it->collection}</h3>
			</th>
			<td>
				<table id="metadata" class="{$it->collectionAsciiId}">
					{foreach item=set key=ascii_id from=$it->metadata}
					<tr>
						<th>{$set.attribute_name}</th>
						<td>
							<ul>
								{foreach item=value from=$set.values}
								<li>
								<a href="search?c={$it->collectionAsciiId}&amp;q={$ascii_id}:&quot;{$value.text|escape:'url'}&quot;">{$value.text} {if $value.mod}({$value.mod}){/if}</a>
								</li>
								{/foreach}
							</ul>
						</td>
						{/foreach}
					</tr>
				</table>
			</td>
		</tr>
		{/foreach}
	</table>
</div>
{/block}
