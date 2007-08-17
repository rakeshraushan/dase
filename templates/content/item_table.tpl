<table class="itemView" id="{$table_id}">
<tr><th>
<img src="media/{$item->collection->ascii_id}/viewitem/{$item->media_file_array.viewitem->filename}" 
width="{$item->media_file_array.viewitem->width}" 
height="{$item->media_file_array.viewitem->height}"
/>

{if 1 == $item->is_editable} {* 1 *}
<div class="replace">
<a href="#" id="replaceThumbSrc" class="tinyAlert">replace thumbnail</a>
<div id="replaceThumbTar" class="hide">
<form action="index.php" class="styled" enctype="multipart/form-data" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="16000000" />
<input type="hidden" name="action" value="replace_thumbs"/>
<input type="hidden" name="collection_id" value="{$collection->id}"/>
<input type="hidden" name="item_id" value="{$item->id}"/>
<input type="hidden" name="tag_item_id" value="{$tag_item->id}"/>
<input type="file" id="fileInput" name="upload_files[]"/> 
<input type="submit" value="replace"/> 
<input type="submit" value="cancel" id="closeReplaceThumb"/> 
</form>
</div>
</div>
{/if}{* /1 *}

{if $item->media_file_array}{* 2 *}
{include file="item_media.tpl"}
{/if}{* /2 *}

</th><td>
{if 1 == $item->is_editable} {* 3 *}
<div class="pageControls">
<a href="#" id="inputTemplateToggle" class="tinyAlert tempHide">[hide/show input template]</a>
</div>
<div class="spacer"></div>
{if 1 == $item->status_id} {* 5 *}
<div class="item_status">This item has NOT been made public [<a href="admin/{$cb}/publish_item/{$item->id}&tag_item_id={$tag_item->id}" class="modify">make it public</a>]</div>
{/if} {* /5 *}
{if 2 == $item->status_id} {* 6 *}
<div class="item_status delete">This item is marked to be deleted [<a href="admin/{$cb}/mark_item_for_delete/{$item->id}&tag_item_id={$tag_item->id}&undo=1">undo</a>]</div>
{/if} {* /6 *}


<div class="addMetadataForm">

<h3>Add Metadata</h3>
{if $user->input_template ne ''} {* 7 *}
<h4>input template</h4>
<form class="styled" id="addTemplateMetadata" method="post">
<input type="hidden" name="action" value="add_item_multi_metadata"/>
<input type="hidden" name="item_id" value="{$item->id}"/>
<input type="hidden" name="tag_item_id" value="{$tag_item->id}"/>
<input type="hidden" name="display" value="{$display}"/>
<input type="hidden" name="start" value="{$start}"/>
<input type="hidden" name="anchor" value="{$anchor}"/>
<input type="hidden" name="collection_ascii_id" value="{$item->collection->ascii_id}"/>
{$user->input_template}
<input type="submit" value="submit"/>
<input type="submit" name="save_values" value="submit and save values"/>
</form>

<!-- strictly so that ajax edit can work in item record -->
<form class="hide">
<select id="attribute_select" name="attribute_id">
<option value="">select an attribute:</option>
{foreach item="att" from=$item->collection->attribute_array}
<option value="{$att->id} {$att->html_input_type_id}">{$att->attribute_name}</option>
{/foreach}
</select>
</form>

<!-- ALSO regular metadata input-->
<h4>single field:</h4>
<form class="styled" id="addMetadata" method="post">
<input type="hidden" name="action" value="add_item_metadata"/>
<input type="hidden" name="item_id" value="{$item->id}"/>
<input type="hidden" name="tag_item_id" value="{$tag_item->id}"/>
<input type="hidden" name="display" value="{$display}"/>
<input type="hidden" name="start" value="{$start}"/>
<input type="hidden" name="anchor" value="{$anchor}"/>
<input type="hidden" name="collection_ascii_id" value="{$item->collection->ascii_id}"/>
<select id="attribute_select" name="attribute_id">
<option value="">select an attribute:</option>
{foreach item="att" from=$item->collection->attribute_array}
<option value="{$att->id} {$att->html_input_type_id}">{$att->attribute_name}</option>
{/foreach}
</select>
<div id="addMetadataInputs" class="addMetadata"></div>
<div id="addMetadataButtons" class="addMetadata"></div>
<div id="usageNotes" class="addMetadata"></div>
</form>
{else} 
<form class="styled" id="addMetadata" method="post">
<input type="hidden" name="action" value="add_item_metadata"/>
<input type="hidden" name="item_id" value="{$item->id}"/>
<input type="hidden" name="tag_item_id" value="{$tag_item->id}"/>
<input type="hidden" name="display" value="{$display}"/>
<input type="hidden" name="start" value="{$start}"/>
<input type="hidden" name="anchor" value="{$anchor}"/>
<input type="hidden" name="collection_ascii_id" value="{$item->collection->ascii_id}"/>
<select id="attribute_select" name="attribute_id">
<option value="">select an attribute:</option>
{foreach item="att" from=$item->collection->attribute_array}
<option value="{$att->id} {$att->html_input_type_id}">{$att->attribute_name}</option>
{/foreach}
</select>
<div id="addMetadataInputs" class="addMetadata"></div>
<div id="addMetadataButtons" class="addMetadata"></div>
<div id="usageNotes" class="addMetadata"></div>
</form>
{/if} {* /7 *}
</div>
{/if} {* /3 *}

<dl class="itemMetadata" {if 1 == $item->is_editable}id="editable"{/if}> {* 8/8 *}

{if 1 == $item->is_editable} {* eee *}
<dt class="item_type">Item Type:</dt>
<dd>
<div class="edit" id="typeFormSrc">{$item->item_type->name|default:'none specified'}</div>
<div id="typeFormTar" class="tempHide">
<form class="styled typeForm" method="post">
<input type="hidden" name="action" value="set_item_type"/>
<input type="hidden" name="item_id" value="{$item->id}"/>
<input type="hidden" name="tag_item_id" value="{$tag_item->id}"/>
<input type="hidden" name="display" value="{$display}"/>
<input type="hidden" name="start" value="{$start}"/>
<input type="hidden" name="anchor" value="{$anchor}"/>
<input type="hidden" name="collection_ascii_id" value="{$item->collection->ascii_id}"/>
{foreach item="it" from=$item->collection->item_type_array}
<input type="radio" name="item_type_id" value="{$it->id}" {if ($item->item_type->id == $it->id)}checked="checked"{/if}/> {$it->name} {if $it->description}<span class="tinyAlert">({$it->description|truncate:70:"...":true})</span>{/if}<br/>
{/foreach}
<input type="radio" name="item_type_id" value="0" {if !$item->item_type->id}checked="checked"{/if}/> none specified<br/>
<input type="submit" value="set item type"/> 
<input type="submit" value="cancel" id="closeTypeForm"/> 

<a href="admin/{$cb}/manage_item_types" class="tinyAlert">[Add/Edit Item Types]</a>
</form>
</div>
</dd>
{else}
{if $item->item_type->id} {* fff *}
<dt>Item Type:</dt>
<dd>{$item->item_type->name|default:'none specified'}</dd>
{/if} {* /fff *}
{/if} {* /eee *}

{foreach item=value from=$item->value_array}
{if $last_att != $value->attribute->attribute_name} {* 9 *}
<dt>{$value->attribute->attribute_name}:</dt>
{/if} {* /9 *}
{assign var="last_att" value=$value->attribute->attribute_name}
<dd>
{if 1 == $item->is_editable} {* 10 *}
<div class="edit {$value->id} {$value->attribute->id} {$value->attribute->html_input_type_id}">{$value->value_text}</div>
{else}
<a href="index.php?action=search&query={$value->encoded_value_text}&attribute_id={$value->attribute->id}&collection_id={$value->attribute->collection_id}">{$value->value_text|markdown}</a>
{/if} {* /10 *}
</dd>
{/foreach}

</dl>
<div class="adminToggle">
<a href="#" id="adminMetadataSrc" class="adminshow">show/hide admin metadata</a>
</div>
<dl id="adminMetadataTar" class="hide">
{foreach item=value from=$item->admin_value_array}
{if $last_admin_att != $value->attribute->attribute_name}
<dt class="admin">{$value->attribute->attribute_name}:</dt>
{/if}
{assign var="last_admin_att" value=$value->attribute->attribute_name}
<dd>
<a href="index.php?action=search&query={$value->encoded_value_text}&attribute_id={$value->attribute->id}&collection_id={$item->collection_id}" class="itemMetadata">{$value->value_text|nl2br}</a>
</dd>
{/foreach}
</dl>


{if 1 == $item->is_editable}
<div class="note">{$user->name} has editing privileges for this item.
{if 1 != $show_revision_history}
<a href="admin/{$item->collection->ascii_id}/item_revision_history/{$item->id}&tag_item_id={$tag_item->id}&display={$display}&start={$start}&anchor={$anchor}">view revision history</a>
{/if}
{if 1 == $show_revision_history}
<a href="view/search_item/{$item->id}/{$display}?tag_item_id={$tag_item->id}&start={$start}&anchor={$anchor}">hide revision history</a>
{foreach item=rev from=$revision_array}
<p>
{$rev->attribute_name}  | old: <span class="deleted">{$rev->deleted_text}</span> new: <span class="added">{$rev->added_text}</span> | by {$rev->dase_user_eid} on {$rev->revised}
</p>
{/foreach}
{/if}
</div>
{/if}

<a href="{$item->collection->ascii_id}/{$item->serial_number}">permalink</a> |
<a href="{$http_app_root}/media/{$item->collection->ascii_id}/thumbnail/{$item->thumbnail_filename}">thumbnail</a> |
<a href="action/link_back_sample/{$item->id}" id="linkBack">link back sample</a> |
<a href="xml/{$item->collection->ascii_id}/{$item->serial_number}">xml</a>
<!--
{if $item->user_editable == 1}
| <a href="action/edit_item/{$item->id}" target="_blank" class="alert">edit item</a>
{/if}
-->

</td></tr>
</table>

{if 1 == $item->is_editable}
{if $item->status_id != 2}
<a href="admin/{$cb}/mark_item_for_delete/{$item->id}?tag_item_id={$tag_item->id}" class="delete">mark this item 'to be deleted'</a>
{/if}
{/if}
