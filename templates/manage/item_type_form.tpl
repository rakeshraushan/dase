{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/item_type_form.js"></script>
{/block}

{block name="head-links}
{if $edit_url}
<!-- atompub -->
<link rel="edit" type="application/atom+xml" href="{$edit_url}"/>
{/if}
{/block}

{block name="title"}DASe: {$collection->collection_name|escape}{/block} 

{block name="content"}
<div id="contentHeader">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Item Types for {$collection->collection_name}</h1>
</div>
<div id="collectionData">
	<div id="browseColumns">
		<div id="catColumn">
			<h4>Item Types:</h4>
			{foreach item=t from=$item_types}
			<a href="manage/{$collection->ascii_id}/item_type/{$t->ascii_id}"
				{if $type->ascii_id == $t->ascii_id}class="spill"{/if}>{$t->name}</a>
			{/foreach}
			<p class="getForm">
			<a class="delete"
				href="manage/{$collection->ascii_id}/item_type_form"
				>new item type form</a>
			</p>
		</div>
		{if $type}
		<div id="attColumn">
			{if $type->ascii_id}
			<h1>Item Type: {$type->name} ({$type->ascii_id})</h1>
			{else}
			<h1>Create An Item Type</h1>
			{/if}
			<form
				id="editType"
				{if $type->ascii_id}
				action="manage/{$collection->ascii_id}/item_type/{$type->ascii_id}" 
				{else}
				action="manage/{$collection->ascii_id}/item_types" 
				{/if}
				method="post">
				<p>
				<label for="name">Name</label>
				<input type="text" name="name" value="{$type->name}"/>
				</p>
				<p>
				<label for="description">Description
					<br/>
					<span class="current">
						{if $type->description}
						[{$type->description}]
						{/if}
					</span>
				</label>
				<textarea type="text" name="description" >{$type->description}</textarea>
				</p>
				<p>
				{if $type->ascii_id}
				<input type="submit" value="update"/>
				<input
				type="submit"
				name="_method"
				id="deleteType"
				class="deleteControl"
				value="delete {$type->name}"/>
				{else}
				<input type="submit" value="create"/>
				{/if}
				</p>
			</form>
			{if $type->ascii_id}
			<div id="atts">
				<h3>Attributes for "{$type->name}" Item Type</h3>
				<form
					id="type_atts_form"
					action="manage/{$collection->ascii_id}/item_type/{$type->ascii_id}/attributes.json" 
					method="post">
					<select name="att_ascii_id">
						<option>select one:</option>
						{foreach item=att from=$attributes}
						<option value="{$att->ascii_id}">{$att->attribute_name}</option>
						{/foreach}
					</select>
					<input type="submit" value="add"/>
				</form>
				<div id="type_atts_list">
					<ul id="deletableAtts"></ul>
				</div>
			</div>
			<div id="related">
				<h3>Item Types related to "{$type->name}" Item Type</h3>
				<form
					id="type_rels_form"
					action="manage/{$collection->ascii_id}/item_type/{$type->ascii_id}/relations.json" 
					method="post">
					<select name="rel_type_ascii_id">
						<option>select one:</option>
						{foreach item=t from=$item_types}
						<option value="{$t->ascii_id}">{$t->name}</option>
						{/foreach}
					</select>
					<select name="rel">
						<option value="child">child</option>
						<option value="parent">parent</option>
					</select>
					<input type="submit" value="add"/>
				</form>
				<div id="type_rels_list">
					<ul id="deletableTypes"></ul>
				</div>
			</div>
			{/if}
		</div>
		{/if}
	</div>
	<div class="spacer"></div>
</div>

<!-- javascript templates for atts-->
<textarea class="javascript_template" id="type_atts_jst">
	{literal}
	{for a in atts}
	<li><a href="manage/${a.collection_ascii_id}/attribute/${a.att_ascii_id}">${a.attribute_name}</a> <a href="manage/${a.collection_ascii_id}/item_type/${a.item_type_ascii}/attribute/${a.att_ascii_id}" class="delete">delete</a></li>
	{/for}
	{/literal}
</textarea>
<!-- end javascript template -->

<!-- javascript templates for rels-->
<textarea class="javascript_template" id="type_rels_jst">
	{literal}
	{for r in rels.parents}
	<li>
	<a href="manage/${r.collection_ascii_id}/item_type/${r.ascii_id}">${r.name}</a> 
	(parent) 
	<a href="manage/${r.collection_ascii_id}/item_type_relation/${r.relation_id}" class="delete">delete</a> 
	{if r.title}
	<p class="relationDesc">${r.title}</p>
	{/if}
	</li>
	{/for}
	{for r in rels.children}
	<li>
	<a href="manage/${r.collection_ascii_id}/item_type/${r.ascii_id}">${r.name}</a> 
	(child) 
	<a href="manage/${r.collection_ascii_id}/item_type_relation/${r.relation_id}" class="delete">delete</a> 
	{if r.title}
	<p class="relationDesc">${r.title}</p>
	{/if}
	</li>
	{/for}
	{/literal}
</textarea>
<!-- end javascript template -->
{/block} 

