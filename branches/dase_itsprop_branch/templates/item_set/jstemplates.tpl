{literal}

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
	<form action="tag/${eid_ascii}/metadata" method="post">
		<input type="text" name="value"/>
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		<input type="submit" value="add"/>
	</form>
</textarea>

<pre class="javascript_template" id="input_form_textarea_jst">
    <form action="tag/${eid_ascii}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
        <p>
		   <textarea name="value"></textarea>
        </p>
		<input type="submit" value="add"/>
	</form>
</pre>

<textarea class="javascript_template" id="input_form_radio_jst">
	<form action="tag/${eid_ascii}/metadata" method="post">
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
	<form action="tag/${eid_ascii}/metadata" method="post">
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
	<form action="tag/${eid_ascii}/metadata" method="post">
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
	<form action="tag/${eid_ascii}/metadata" method="post">
		<input type="hidden" name="${ascii_id}"/>
        <p>
		<textarea name="values"></textarea>
        </p>
		<input type="submit" value="add"/>
	</form>
</pre>

<textarea class="javascript_template" id="input_form_no_edit_jst">
	<form action="tag/${eid_ascii}/metadata" method="post">
		<input type="hidden" name="ascii_id" value="${ascii_id}"/>
		<input type="text" disabled="disabled" name="value"/>
		<input type="submit" value="add"/>
	</form>
</textarea>

<textarea class="javascript_template" id="input_form_text_with_menu_jst">
	<form action="tag/${eid_ascii}/metadata" method="post">
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
