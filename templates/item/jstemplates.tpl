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

<textarea class="javascript_template" id="input_form_textarea_jst">
	<form action="item/${coll_ser}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		<textarea name="value"></textarea>
		<input type="submit" value="add"/>
	</form>
</textarea>

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
		<select name="value"/>
			{for v in values }
			<option value="${v}"/>${v}</option>
			{/for}
			</p>
			<input type="submit" value="add"/>
		</form>
	</textarea>

	<textarea class="javascript_template" id="input_form_listbox_jst">
		<form action="item/${coll_ser}/metadata" method="post">
			<input type="hidden" name="${ascii_id}"/>
			<textarea name="values"></textarea>
			<input type="submit" value="add"/>
		</form>
	</textarea>

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
			<select name="value"/>
				{for v in values }
				<option value="${v}"/>${v}</option>
				{/for}
				</p>
			</form>
		</textarea>
		{/literal}
