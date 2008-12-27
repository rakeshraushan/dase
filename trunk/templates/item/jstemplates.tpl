{literal}

<!-- metadata -->

<textarea class="javascript_template" id="metadata_jst">
	{for m in meta}
	{if m.collection_id != 0 }
	{if seen != m.attribute_name}
	<dt>${m.attribute_name}</dt>
	{/if}
	<dd>${m.value_text}</dd>
	{var seen = m.attribute_name}
	{/if}
	{/for}
</textarea>

<!-- set parent link form -->

<textarea class="javascript_template" id="parent_link_jst">
	<h1>attach to ${parent} (${count})</h1>
	<form id="setParentForm" action="xxxxxxxxx">
	<select name="parent_ascii_id">
	<option>select one:</option>
	{for item in items}
	<option value="${item.serial_number}">${item.title}</option>
	{/for}
	</select>
	<input type="submit" value="create link"/>
	</form>
</textarea>

<!-- item status form -->

<textarea class="javascript_template" id="item_status_jst">
	<p>This item is <span class="current">${status.label}</span></p> 
	<form id="updateStatus">
		<select name="status">
			<option value="public" {if status.term == 'public'}selected="selected"{/if}>Public</option>
			<option value="draft" {if status.term == 'draft'}selected="selected"{/if}>Draft (Admin View Only)</option>
			<option value="delete" {if status.term == 'delete'}selected="selected"{/if}>Marked for Deletion</option>
			<option value="archive" {if status.term == 'archive'}selected="selected"{/if}>In Deep Storage</option>
		</select>
		<input type="submit" value="update status"/>
	</form>
</textarea>

<!-- textual content form -->
<!-- note: cannot iterate properly w/in pre tag. urghhhh -->

<pre class="javascript_template" id="textual_content_jst">
	<h1>Add/Edit Textual Content</h1>
	<form action="item/${coll_ser}/content" method="post" id="textualContentForm">
		<p>
            {if content.latest.text}
			<h4>last updated ${content.latest.date}</h4>
            {/if}
			<textarea cols="50" rows="15" name="content">${content.latest.text}</textarea>
		</p>
        <p>
            <input type="submit" value="update"/>
        </p>
	</form>
</pre>

<!-- item type form -->

<textarea class="javascript_template" id="item_type_jst">
	<h1>Set Item Type</h1>
 	<h3 id="currentItemType">Current Item Type: ${current}</h3>
	<form action="item/${coll_ser}/item_type" method="post" id="itemTypeForm">
		<p>
			<select name="item_type">
			<option>select one:</option>
			{for t in types}
			<option value="${t.ascii_id}">${t.name}</option>
			{/for}
			</select>
			<input type="submit" value="set"/>
		</p>
	</form>
</textarea>

<!-- form to request input form -->

<textarea class="javascript_template" id="select_att_jst">
	<h1>Add Metadata</h1>
	<form action="ss" method="get" id="getInputForm">
		<select name="att_ascii_id">
			<option value="">select an attribute</option>
			{for att in atts}
			<option value="${att.ascii_id}">${att.attribute_name}</option>
			{/for}
		</select>
	</form>
	<div id="addMetadataFormTarget"><!--input from will go here--></div>
</textarea>

<!-- input forms -->

<textarea class="javascript_template" id="input_form_text_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="text" name="value"/>
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		<input type="submit" value="add"/>
	</form>
</textarea>

<pre class="javascript_template" id="input_form_textarea_jst">
    <form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
        <p>
		   <textarea name="value"></textarea>
        </p>
		<input type="submit" value="add"/>
	</form>
</pre>

<textarea class="javascript_template" id="input_form_radio_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		{for v in values}
		<p>
		<input type="radio" name="value[]" value="${v}"/> ${v}
		</p>
		{/for}
		<p>
		<input type="submit" value="add"/>
		</p>
	</form>
</textarea>

<textarea class="javascript_template" id="input_form_checkbox_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		{for v in values}
		<p>
		<input type="checkbox" name="value[]" value="${v}"/> ${v}
		</p>
		{/for}
		<p>
		<input type="submit" value="add"/>
		</p>
	</form>
</textarea>

<textarea class="javascript_template" id="input_form_select_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		<p>
		<select name="value">
			{for v in values }
			<option value="${v}"/>${v}</option>
			{/for}
		</select>
		</p>
		<input type="submit" value="add"/>
	</form>
</textarea>

<pre class="javascript_template" id="input_form_listbox_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="${ascii_id}"/>
        <p>
		<textarea name="values"></textarea>
        </p>
		<input type="submit" value="add"/>
	</form>
</pre>

<textarea class="javascript_template" id="input_form_no_edit_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		<input type="text" disabled="disabled" name="value"/>
		<input type="submit" value="add"/>
	</form>
</textarea>

<textarea class="javascript_template" id="input_form_text_with_menu_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		<input type="text" name="value"/>
		<input type="submit" value="add"/>
		<p>
		<select name="value">
			{for v in values }
			<option value="${v}"/>${v}</option>
			{/for}
		</select>
		</p>
	</form>
</textarea>
{/literal}
