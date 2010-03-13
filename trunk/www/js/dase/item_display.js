Dase.pageInit = function() {
	Dase.initNotes();
}

//item editing needs to know user, 
//so we use the 'pageInitUser' function
Dase.pageInitUser = function(eid) {
	Dase.initAddAnnotation();
	Dase.recordItemView(eid);
	if ('hide' == Dase.user.controls) return;
	var auth_info = Dase.checkAdminStatus(eid);
	if (!auth_info) {
		return;
	};
	var controls = Dase.$('adminPageControls');
	if (auth_info.auth_level == 'manager' || auth_info.auth_level == 'admin' || auth_info.auth_level == 'write')
	{
		Dase.removeClass(controls,'hide');
		Dase.initEditMetadata();
		Dase.initAddMetadata();
		Dase.initAddContent();
		Dase.initSetItemType();
		Dase.initSetItemStatus();
		Dase.initReindexItem();
		Dase.initSaveItemTo();
	}
	return;
};

Dase.initSaveItemTo = function() {
	var user = Dase.user;
	//save to select menu
	var h = new Dase.htmlbuilder;
	var sel = h.add('select',{'id':'saveToSelect','name':'collection_ascii_id'});
	sel.add('option',{'value':''},'save item to...');
	for (var n in user.tags) {
		var tag = user.tags[n];
		if ('admin' != tag.type) {
			var opt = sel.add('option',{'value':tag.ascii_id});
			opt.setText(tag.name+' ('+tag.item_count+')');
		}
	}
	var inp = h.add('input',{'type':'submit','value':'add'});
	if (Dase.$('saveChecked')) {
		h.attach(Dase.$('saveChecked'));
	}

	var form = Dase.$('saveToForm');
	if (!form) return;
	form.onsubmit = function() {
		var saveToSelect = Dase.$('saveToSelect');
		var tag_ascii_id = saveToSelect.options[saveToSelect.options.selectedIndex].value;
		var item_uniques_array = [];
		if (!tag_ascii_id) {
			alert('Please select a user collection/slideshow/cart to save items to.');
			return false;
		}
		item_unique = Dase.$('item_unique').value; 
		Dase.ajax(Dase.base_href + 'tag/' + Dase.user.eid + "/"+tag_ascii_id,'POST',
		function(resp) { 
			alert(resp); 
			Dase.initUser();
			//Dase.initSaveTo();
		},item_unique);
		return false;
	};
	//why this?
	//Dase.initCreateNewSet();
};

Dase.recordItemView = function(eid) {
	var pageurl = encodeURIComponent(location.href.replace(Dase.base_href,''));
	var title = encodeURIComponent(Dase.getMeta('item-title'));
	var content_headers = {
		'Content-Type':'application/x-www-form-urlencoded'
	}
	var url = Dase.base_href+'user/'+Dase.user.eid+'/recent';
	var pairs = 'url='+pageurl+'&'+'title='+title;
	Dase.ajax(url,'post',null,pairs,Dase.user.eid,Dase.user.htpasswd,content_headers); 
}


Dase.sortByTitle = function(a,b) {
	var x = a.title.toLowerCase();
	var y = b.title.toLowerCase();
	if (x < y) return -1
	if (x > y) return 1
	return 0

}

//from item_set_display
Dase.getInputForm = function(resp) {
	var vals = resp.values;
	var form = new Dase.htmlbuilder('form',{'method':'post'});
	form.set('action','item/'+resp.coll_ser+'/metadata');
	form.add('input',{'type':'hidden','name':'ascii_id','value':resp.ascii_id});
	switch(resp.html_input_type) {
		case 'text':
		form.add('input',{'type':'text','name':'value'});
		break;
		case 'textarea':
	form.add('p').add('textarea',{'name':'value'},' ');
	break;
	case 'radio':
	for (var i=0;i<vals.length;i++) {
		var v = vals[i];
		var p = form.add('p');
		p.add('input',{'type':'radio','name':'value[]','value':v});
		p.add('span',null,' '+v);
	}
	break;
	case 'checkbox':
	for (var i=0;i<vals.length;i++) {
		var v = vals[i];
		var p = form.add('p');
		p.add('input',{'type':'checkbox','name':'value[]','value':v});
		p.add('span',null,' '+v);
	}
	break;
	case 'select':
	var sel = form.add('p').add('select',{'name':'value'});
	sel.add('option',{'value':''},'select one:');
	for (var i=0;i<vals.length;i++) {
		var v = vals[i];
		sel.add('option',{'value':v},v);
	}
	break;
	case 'listbox':
form.add('p').add('textarea',{'name':'value'},' ');
break;
case 'no_edit':
form.add('input',{'type':'text','name':'value','disabled':'disabled'});
break;
case 'text_with_menu':
form.add('input',{'type':'text','name':'value','id':'autofill_target'});
var sel = form.add('p').add('select',{'name':'blank','id':'select_autofill'});
sel.add('option',{'value':''},'select one:');
for (var i=0;i<vals.length;i++) {
	var v = vals[i];
	sel.add('option',{'value':v},v);
}
break;
	}
	form.add('input',{'type':'submit','value':'add'});
	return form.getString();
}

Dase.initAddAnnotation = function() {
	var owner = Dase.getMeta('tagOwner');
	if (Dase.user.eid != owner) return;
	var tog = Dase.$('annotationToggle');
	if (!tog) return;
	Dase.removeClass(tog,'hide');
	tog.onclick = function() {
		Dase.toggle(Dase.$('annotationText'));
		Dase.toggle(Dase.$('setAnnotationForm'));
		return false;
	};
};

Dase.initSetItemStatus = function() {
	var status_link = Dase.$('setItemStatusLink');
	var status_form = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	if (!status_link || !status_form) return;
	status_link.onclick = function() {
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
		if (Dase.toggle(status_form)) {
			var status = Dase.$('itemStatus').innerHTML;
			var h = new Dase.htmlbuilder;
			h.add('h1',null,'Item Status ('+status+')');
			var form = h.add('form',{'id':'itemStatusForm'});
			var sel = form.add('select',{'name':'status'});
			sel.add('option',null,'select status:');
			sel.add('option',{'value':'public'},'public');
			sel.add('option',{'value':'draft'},'draft');
			sel.add('option',{'value':'delete'},'delete');
			sel.add('option',{'value':'archive'},'archive');
			form.add('input',{'type':'submit','value':'update status'});
			form.add('span',null,' ');
			form.add('span',{'id':'updateMsg'});
			h.attach(status_form);
			Dase.initItemStatusForm(Dase.$('itemStatusForm'));
		}
		return false;
	};

};

Dase.initReindexItem = function() {
	var link = Dase.$('reindexLink');
	if (!link) return;
	link.onclick = function() {
		var orig = Dase.$('metadata').innerHTML;
		Dase.$('metadata').innerHTML = '<h2 class="alert">reindexing item...</h2>';
		Dase.ajax(this.href,'post',function(resp) { 
			Dase.$('metadata').innerHTML = orig;
		},null,Dase.user.eid,Dase.user.htpasswd);
		return false;
	};

};

Dase.initItemStatusForm = function(form) {
	var edit_url = Dase.atompub.getJsonEditLink();
	var atom_json;
	form.onsubmit = function() {
		Dase.$('updateMsg').innerHTML = "updating status...";
		Dase.getJSON(edit_url,function(json){
			atom_json = json;
			var target_scheme = 'http://daseproject.org/category/status';
			for (var i=0;i<atom_json.category.length;i++) {
				var cat = atom_json.category[i];
				if (cat.scheme == target_scheme) {
					atom_json.category[i].term = form.status.options[form.status.selectedIndex].value;
				}
			}
			Dase.atompub.putJson(Dase.atompub.getEditLink(),atom_json,function(resp) {
				Dase.pageReload(resp+' ('+Dase.atompub.getDate()+')');
			},Dase.user.eid,Dase.user.htpasswd);
		},Dase.user.eid,Dase.user.htpasswd);
		return false;
	};
};

Dase.initNotes = function() {
	Dase.getNotes();
	var notes = Dase.$('notes');
	var notes_link = Dase.$('notesLink');
	if (!notes) return;
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
		notes.innerHTML = '<h3 class="updating">updating...</h3>';
		Dase.toggle(notesForm);
		var note = Dase.$('note').value;
		Dase.ajax(document.notes_form.action,'POST',
		function(resp) { 
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
			html += '<li class="note">'
			html += '<h3><span class="username">'+resp[i].eid+'</span> '+resp[i].updated+'</h3>';
			html +=	resp[i].text.replace(/\n/g,'<br/>');
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

Dase.initEditMetadata = function() {
	var link = Dase.$('editMetadataLink');
	var metadata = Dase.$('metadata');
	var form_div = Dase.$('ajaxFormHolder');
	if (!metadata || !form_div || !link) return;
	link.onclick = function() {
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.toggle(metadata);
		Dase.toggle(form_div);
		form_div.innerHTML = '<h1>loading form...</h1>';
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
		Dase.getJSON(link.href,function(json) {
			//build form and insert it into page
			form_div.innerHTML = '<h1 id="formText">loading form...</h1><div id="editMetadata">'+Dase.buildEditMetadataForm(json)+'</div>';
			var forms = form_div.getElementsByTagName('form');
			for (var i=0;i<forms.length;i++) {
				if (forms[i].del) {
					forms[i].del.onclick = function() {
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
								},null,Dase.user.eid,Dase.user.htpasswd);
								return false;
							};
						} else {
							return false;
						}
					};
				}
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
					var value_id = this.className;
					Dase.addClass(this,'updated');
					Dase.ajax(this.action,'put',function(resp) { 
						var value_text = resp;
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
					},value_text,Dase.user.eid,Dase.user.htpasswd); 
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
	var atts_link = Dase.getLinkByRel('http://daseproject.org/relation/attributes');
	if (!mlink || !mform) return;
	mlink.onclick = function() {
		//alert(atts_link);
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
		if (Dase.toggle(mform)) {
			mform.innerHTML = '<h1 class="loading">Loading...</h1>';
			Dase.getJSON(this.href, function(atts){
				h = new Dase.htmlbuilder;
				//individual attribute
				h.add('h1',null,'Add Metadata Individually');
				var form = h.add('form',{'action':'sss','method':'get','id':'getInputForm'});
				var sel = form.add('select',{'name':'att_ascii_id'});
				sel.add('option',{'value':''},'select an attribute');
				for (var i=0;i<atts.length;i++) {
					var att = atts[i];
					sel.add('option',{'value':att.href},att.attribute_name);
				}
			h.add('div',{'id':'addMetadataFormTarget'},'&nbsp;');

			//input template
			var tdiv = h.add('div',{'id':'tdiv'});
			tdiv.add('h1',null,'Add Metadata by Input Template');

			h.attach(mform);

			//individual attribute
			var getForm = Dase.$('getInputForm');
			Dase.initGetInputForm(getForm);

			//input template
			var inputTempDiv = Dase.$('tdiv');
			Dase.initInputTemplate(inputTempDiv);
		});
	}
	return false;
}
};

Dase.initInputTemplate = function(target) {
	var url = Dase.getLinkByRel('http://daseproject.org/relation/input_template');
	Dase.ajax(url,'get',function(resp) {
		target.innerHTML += resp;
		Dase.initTemplateTextWithMenu(target);
	});
};


Dase.initTemplateTextWithMenu = function(target) {
	//activate all text_with_menu elements in input template
	var inps = target.getElementsByTagName('input');
	for (var i=0;i<inps.length;i++) {
		var text_target = inps[i];
		if ('autofill_target' == text_target.className) {
			dynamic_select = Dase.$('autofill_select_'+text_target.name);
			if (dynamic_select) {
				dynamic_select.text_target = text_target;
				dynamic_select.onchange = function() {
					this.text_target.value = this.options[this.selectedIndex].value;
				}
			}
		}
	}
};


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
			Dase.getJSON(this.href, function(content){
				var coll_ser = Dase.$('collSer').innerHTML;
				var h = new Dase.htmlbuilder;
				h.add('h1',null,'Add/Edit Textual Content');
				var form = h.add('form',{'action':'item/'+coll_ser+'/content','method':'post','id':'textualContentForm'});
				if (content.latest.text) {
					form.add('h4',null,'last updated '+content.latest.date);
				}
			form.add('p').add('textarea',{'cols':'50','rows':'15','name':'content'},content.latest.text+' ');
			form.add('p').add('input',{'type':'submit','value':'update'});
			h.attach(cform);
			var contentForm = Dase.$('textualContentForm');
			Dase.initContentForm(contentForm);
		});
	}
	return false;
};
};

Dase.initSetItemType = function()
{
	var type_link = Dase.$('setItemTypeLink');
	var type_form = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	if (!type_link || !type_form) return;
	type_link.onclick = function() {
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
		if (Dase.toggle(type_form)) {
			type_form.innerHTML = '<h1 class="loading">Loading...</h1>';
			Dase.getJSON(this.href, function(json){
				var data = {};
				var current_elem = Dase.$('itemType');
				if (current_elem) {
					data.current = current_elem.innerHTML;
				} else {
					data.current = 'default/none';
				}
				data.coll_ser = Dase.$('collSer').innerHTML;
				data.types = json;
				var h = new Dase.htmlbuilder;
				h.add('h1',null,'Set Item Type '+data.current);
				//hmm URL construction instead of HATEAOS
				var form = h.add('form',{'action':'item/'+data.coll_ser+'/item_type','method':'post','id':'itemTypeForm'});
				var select = form.add('select',{'name':'item_type'});
				select.add('option',null,'select one:');
				for (var i=0;i<data.types.length;i++) {
					var t = data.types[i];
					select.add('option',{'value':t.ascii_id},t.name);
				}
				form.add('input',{'type':'submit','value':'set'});
				form.add('span',null,' ');
				form.add('span',{'id':'updateMsg'});
				h.attach(type_form);
				Dase.initItemTypeForm(Dase.$('itemTypeForm'));
			});
		}
		return false;
	};
};

Dase.initItemTypeForm = function(form) {
	form.onsubmit = function() {
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.$('updateMsg').innerHTML = 'updating...';
		Dase.ajax(this.action,'post',function(resp) { 
			Dase.pageReload(resp);
		},Dase.form.serialize(this),null,null,content_headers); 
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
			Dase.pageReload(resp);
		},Dase.form.serialize(this),null,null,content_headers); 
		Dase.toggle(Dase.$('ajaxFormHolder'));
		return false;
	}
};

Dase.initGetInputForm = function(form) {
	coll = Dase.$('collectionAsciiId').innerHTML;
	form.att_ascii_id.onchange = function() { //this is the attribute selector
	Dase.$('addMetadataFormTarget').innerHTML = 'loading form...';
	var url = this.options[this.selectedIndex].value+'.json';
	Dase.getJSON(url,function(resp) {
		resp.coll_ser = Dase.$('collSer').innerHTML;
		if (!resp.html_input_type) {
			resp.html_input_type = 'text';
		}
		Dase.$('addMetadataFormTarget').innerHTML = Dase.getInputForm(resp);

		var select_autofill = Dase.$('select_autofill');
		if  (select_autofill) {
			select_autofill.onchange = function() {
				Dase.$('autofill_target').value = this.options[this.selectedIndex].value;
			}
		}

		var input_form = Dase.$('ajaxFormHolder').getElementsByTagName('form')[1];
		input_form.onsubmit = function() {
			Dase.addClass(Dase.$('pageReloader'),'hide');
			var content_headers = {
				'Content-Type':'application/x-www-form-urlencoded'
			}
			Dase.loadingMsg(true);
			Dase.ajax(this.action,'post',function() { 
				Dase.removeClass(Dase.$('pageReloader'),'hide');
				Dase.getJSON(Dase.base_href+'item/'+Dase.$('collSer').innerHTML+'/metadata',function(meta) {
					var h = new Dase.htmlbuilder;
					var seen;
					for (var i=0;i<meta.length;i++) {
						var m = meta[i];
						if (m.collection_id != 0) {
							if (seen != m.attribute_name) {
								h.add('dt',null,m.attribute_name);
								seen = m.attribute_name;
							}
							h.add('dd',null,m.value_text)
						}
					}
					h.attach(Dase.$('metadata'));
				});
			},Dase.form.serialize(this),null,null,content_headers); 
			Dase.$('addMetadataFormTarget').innerHTML = '';
			form.att_ascii_id.selectedIndex = 0; //reset attribute selector
			return false;
		};
	});
	return false;
}
};

Dase.buildEditMetadataForm = function(json) {
	var html_form = '';
	for (var i=0;i<json.length;i++) {
		if (json[i].collection_id) { //filters out admin atts which have collection_id 0
		html_form += '<form method="post" id="form_'+json[i].value_id+'" class="'+json[i].value_id+'" action="'+json[i].url+'">';
		html_form += '<label id="label_'+json[i].value_id+'" for="'+json[i].att_ascii_id+'">'+json[i].attribute_name+'</label>';
		html_form += '<p>'+Dase.getFormElement(json[i])+' <input type="submit" "value="update"> <input class="'+json[i].value_id+'" name="del" type="submit" value="delete"></p>';
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
