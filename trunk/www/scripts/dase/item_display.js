Dase.pageInit = function() {
	Dase.initNotes();
}

Dase.initItemEditing = function(eid) {
	var auth_info = Dase.checkAdminStatus(eid);
	if (!auth_info) return;
	var edit_link = Dase.$('editLink');
	if (!edit_link) return;
	var templates_url = Dase.$('jsTemplatesUrl').href;
	if (!templates_url) return;
	var controls = Dase.$('adminPageControls');
	if (auth_info.auth_level == 'manager' || auth_info.auth_level == 'superuser' || auth_info.auth_level == 'write')
	{
		Dase.removeClass(controls,'hide');
		//get jstemplates by making an ajax request
		//see templates/item/jstemplates.tpl
		Dase.ajax(templates_url,'get',function(resp) {
			Dase.$('jsTemplates').innerHTML = resp;
			Dase.updateItemStatus();
			Dase.initEditMetadata(edit_link);
			Dase.initAddMetadata();
			Dase.initAddContent();
		});
	}
	return;
};

Dase.updateItemStatus = function() {
	var status_controls = Dase.$('adminStatusControls');
	Dase.getJSON(Dase.base_href+status_controls.className+'.json',function(json){
			var data = {'status':json};
			var templateObj = TrimPath.parseDOMTemplate("item_status_jst");
			status_controls.innerHTML = templateObj.process(data);
			var form = Dase.$('updateStatus');
			form.onsubmit = function() {
			Dase.ajax(Dase.base_href+status_controls.className,'put',function(resp) {
				Dase.updateItemStatus();
				},form.status.value);
			return false;
			}
			});
}

Dase.initNotes = function() {
	Dase.getNotes();
	var notes = Dase.$('notes');
	var notes_link = Dase.$('notesLink');
	if (!notes_link) return;
	var notesForm = Dase.$('notesForm');
	notes_link.onclick = function() {
		if (notesForm) {
			Dase.toggle(notesForm);
			Dase.$('note').value = '';
		}
		return false;
	};
	notesForm.onsubmit = function() {
		var note = Dase.$('note').value;
		Dase.ajax(document.notes_form.action,'POST',
		function(resp) { 
			Dase.toggle(notesForm);
			Dase.getNotes();
		},note);
		return false;
	};
};

Dase.getNotes = function() {
	var notes = Dase.$('notes');
	var notesLink = Dase.$('notesLink');
	if (!notesLink) return;
	Dase.getJSON(notesLink.href,
	function(resp) {
		var html = '';
		for (var i=0;i<resp.length;i++) {
			html += '<li class="note">'+resp[i].text.replace(/\n/g,'<br/>');
			html += ' <a href="'+notesLink.href+'/'+resp[i].id+'" class="delete note">(x)</a>';
			html += '</li>';
		}
		Dase.$('notes').innerHTML = html;
		var delete_links = notes.getElementsByTagName('a');
		for (var i=0;i<delete_links.length;i++) {
			if ('delete note' == delete_links[i].className) {
				delete_links[i].onclick = function() {
					if (!confirm('delete this note?')) {
						return false;
					}
					Dase.ajax(this.href,'DELETE',
					function(resp) {
						Dase.getNotes();
					},null);
					return false;
				};
			}
		}
	});
};

Dase.initEditMetadata = function(el) {
	var metadata = Dase.$('metadata');
	var form_div = Dase.$('ajaxFormHolder');
	if (!metadata) return;
	if (!form_div) return;
	el.onclick = function() {
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.toggle(metadata);
		Dase.toggle(form_div);
		form_div.innerHTML = '<h1>loading form...</h1>';
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
		Dase.getJSON(el.href,function(json) {
			//build form and insert it into page
			form_div.innerHTML = '<h1 id="formText">loading form...</h1><div id="editMetadata">'+Dase.buildEditMetadataForm(json,el.href)+'</div>';
			var forms = form_div.getElementsByTagName('form');
			for (var i=0;i<forms.length;i++) {
				forms[i].delete.onclick = function() {
					var att_name = Dase.$('label_'+this.className).innerHTML;
					var val_text = Dase.$('val_'+this.className).value;
					if (!val_text) { val_text = ''; }
					if (confirm("confirm delete\n\n"+att_name+"\n"+val_text)) {
						//going up dom a bit too fragile??
						//myform = this.parentNode.parentNode;
						var myform = Dase.$('form_'+this.className);
						myform.onsubmit = function() {
							Dase.addClass(myform,'hide');
							Dase.ajax(this.action,'delete',function(resp) { 
							//	alert(resp);
							});
							return false;
						}
					} else {
						return false;
					}
				};
				forms[i].onsubmit = function() {
					var value_text = '';
					for (var k=0;k<this.elements.length;k++) {
						if ('text' == this.elements[k].type || 'textarea' == this.elements[k].type) {
							value_text = this.elements[k].value;
							break;
						}
						if ('radio' == this.elements[k].type && this.elements[k].checked) {
							value_text = this.elements[k].value;
							break;
						}
					}
					//Dase.loadingMsg(true);
					//handle delete case
					this.className = 'updated';
					Dase.ajax(this.action,'put',function(resp) { 
						var parts = resp.split('|');
						var value_id = parts[0];
						var value_text = parts[1];
						Dase.$('val_'+value_id).value = value_text;
						Dase.$('val_'+value_id).size = value_text.length;
						Dase.$('val_'+value_id).onfocus = function() {
							Dase.removeClass(Dase.$('label_'+value_id),'updated');
						};
						//handle radio buttons
						var radios = Dase.$('val_'+value_id).getElementsByTagName('input');
						for (var j=0;j<radios.length;j++) {
							radios[j].onfocus = function() {
								Dase.removeClass(Dase.$('label_'+value_id),'updated');
							}
						}
						Dase.$('label_'+value_id).className = 'updated';
						Dase.highlight(Dase.$('form_'+value_id),500,'updated');
					},value_text); 
					return false;
				}
			}
			Dase.$('formText').innerHTML = 'Edit Metadata';
		});
		return false;
	};
};

Dase.initAddMetadata = function()
{
	var mlink = Dase.$('addMetadataLink');
	var mform = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	if (!mlink || !mform) return;
	mlink.onclick = function() {
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
		if (Dase.toggle(mform)) {
			mform.innerHTML = '<h1 class="loading">Loading...</h1>';
			Dase.getJSON(this.href, function(json){
			    var data = { 'atts': json };
				var templateObj = TrimPath.parseDOMTemplate("select_att_jst");
				mform.innerHTML = templateObj.process(data);
				var getForm = Dase.$('getInputForm');
				Dase.initGetInputForm(getForm);
			});
		}
		return false;
	}
}

Dase.initAddContent = function()
{
	var clink = Dase.$('addContentLink');
	var cform = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	if (!clink || !cform) return;
	clink.onclick = function() {
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
		if (Dase.toggle(cform)) {
			cform.innerHTML = '<h1 class="loading">Loading...</h1>';
			Dase.getJSON(this.href, function(json){
				//note: we do not use versions in jst due to problems iterating w/in pre tag
			    var data = { 'content': json };
				data.coll_ser = Dase.$('collSer').innerHTML;
				var templateObj = TrimPath.parseDOMTemplate("textual_content_jst");
				cform.innerHTML = templateObj.process(data);
				var contentForm = Dase.$('textualContentForm');
				Dase.initContentForm(contentForm);
			});
		}
		return false;
	}
};

//for adding textual content (atom:content) 
Dase.initContentForm = function(form) {
	form.onsubmit = function() {
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		var cont = Dase.$('itemContent');
		if (cont) {
		   cont.innerHTML = "<h2>Loading content...</h2>";
		}
		Dase.ajax(this.action,'post',function(resp) { 
			Dase.pageReload();
		},Dase.form.serialize(this),null,null,content_headers); 
		Dase.toggle(Dase.$('ajaxFormHolder'));
		return false;
	}
};

Dase.initGetInputForm = function(form) {
	coll = Dase.$('collectionAsciiId').innerHTML;
	form.att_ascii_id.onchange = function() { //this is the attribute selector
		var url = Dase.base_href+'attribute/'+coll+'/'+this.options[this.selectedIndex].value+'.json';
		Dase.getJSON(url,function(resp) {
			resp.coll_ser = Dase.$('collSer').innerHTML;
			if (!resp.html_input_type) {
				resp.html_input_type = 'text';
			}
			var templateObj = TrimPath.parseDOMTemplate('input_form_'+resp.html_input_type+'_jst');
			Dase.$('addMetadataFormTarget').innerHTML = templateObj.process(resp);
			var input_form = Dase.$('ajaxFormHolder').getElementsByTagName('form')[1];
			input_form.onsubmit = function() {
				var content_headers = {
					'Content-Type':'application/x-www-form-urlencoded'
				}
				Dase.loadingMsg(true);
				Dase.ajax(this.action,'post',function() { 
					Dase.getJSON(Dase.base_href+'item/'+Dase.$('collSer').innerHTML+'/metadata',function(metadata_json) {
					data = {'meta':metadata_json};
					var templateObj = TrimPath.parseDOMTemplate('metadata_jst');
					Dase.$('metadata').innerHTML = templateObj.process(data);
				});
			},Dase.form.serialize(this),null,null,content_headers); 
			Dase.$('addMetadataFormTarget').innerHTML = '';
			form.att_ascii_id.selectedIndex = 0; //reset attribute selector
			return false;
		};
	});
	return false;
}
}

Dase.buildEditMetadataForm = function(json,href) {
	var html_form = '';
	for (var i=0;i<json.length;i++) {
		if (json[i].collection_id) { //filters out admin atts which have collection_id 0
		html_form += '<form method="post" id="form_'+json[i].value_id+'" action="'+href+'/'+json[i].value_id+'">';
		html_form += '<label id="label_'+json[i].value_id+'" for="'+json[i].att_ascii_id+'">'+json[i].attribute_name+'</label>';
		html_form += '<p>'+Dase.getFormElement(json[i])+' <input type="submit" value="update"> <input class="'+json[i].value_id+'" name="delete" type="submit" value="delete"></p>';
		html_form += "</form>";
		}
	}
	return html_form;
};

Dase.getFormElement = function(set) {
	var element_html = '';
	var type = set.html_input_type;
	var value_id = set.value_id;
	var name = set.att_ascii_id;
	var value = set.value_text;
	var values = set.values;
	if (value.length > 50) {
		type = 'textarea';
	}

	switch (type) {
		case 'checkbox':
		case 'select':
		case 'radio':
		element_html += '</p><fieldset class="buttonSet" id="val_'+value_id+'">'; //allows this set of buttons to take focus
		for (var i=0;i<values.length;i++) {
			if (value == values[i]) {
				element_html += '<p><input type="radio" class="val_'+value_id+'" checked="checked" name="value_text" value="'+values[i]+'"/> '+values[i]+'</p>';
			} else {
				element_html += '<p><input class="val_'+value_id+'" type="radio" name="value_text" value="'+values[i]+'"/> '+values[i]+'</p>';
			}
		}
		element_html += '</fieldset><p>';
		break;
		case 'text': 
		element_html += '<input type="text" id="val_'+value_id+'" name="value_text" value="'+value+'" size="'+value.length+'"/>';
		break;
		case 'textarea': 
		element_html += '<textarea id="val_'+value_id+'" name="value_text" rows="5">'+value+'</textarea>';
		break;
		case 'no_edit': 
		element_html += value;
		break;
		case 'listbox': 
		element_html += '<input type="text" id="val_'+value_id+'" name="value_text" value="'+value+'" size="'+value.length+'"/>';
		break;
		case 'text_with_menu':
		element_html += '<input type="text" id="val_'+value_id+'" name="value_text" value="'+value+'" size="'+value.length+'"/>';
		break;
		default:
		element_html += '<input type="text" id="val_'+value_id+'" name="value_text" value="'+value+'" size="'+value.length+'"/>';
	}
	return element_html;
};
