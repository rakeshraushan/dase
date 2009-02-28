Dase.pageInit = function() {
	Dase.initNotes();
}

//item editing needs to know user, 
//so we use the 'pageInitUser' function
Dase.pageInitUser = function(eid) {
	if ('hide' == Dase.user.controls) return;
	var auth_info = Dase.checkAdminStatus(eid);
	if (!auth_info) return;
	var controls = Dase.$('adminPageControls');
	if (auth_info.auth_level == 'manager' || auth_info.auth_level == 'superuser' || auth_info.auth_level == 'write')
	{
		Dase.removeClass(controls,'hide');
		Dase.initEditMetadata();
		Dase.initAddMetadata();
		Dase.initAddContent();
		Dase.initSetItemType();
		Dase.initSetItemStatus();
		Dase.initAddAnnotation();
		Dase.initSetParent(controls);
		Dase.initRemoveParents();
	}
	return;
};

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
form.add('input',{'type':'text','name':'blank','id':'autofill_target'});
var sel = form.add('p').add('select',{'name':'value','id':'select_autofill'});
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


Dase.initRemoveParents = function() {
	var edit_url = Dase.atompub.getEditLink();
	var edit_json_url = Dase.atompub.getJsonEditLink();
	var pars = Dase.$('parentLinks');
	if (!pars) return;
	var links = pars.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		if ('hide' == links[i].className) {
			links[i].className = 'delete';
			links[i].onclick = function() {
				if (confirm('are you sure?')) {
					var href = this.href;
					this.innerHTML = 'deleting parent link...';
					this.className = 'modify';
					var rel = 'http://daseproject.org/relation/parent';
					Dase.getJSON(edit_json_url,function(atom_json){
						for (var j=0;j<atom_json.link.length;j++) {
							if ((href == atom_json.link[j].href) && (rel == atom_json.link[j].rel)) {
								atom_json.link[j].rel = 'remove';
								Dase.atompub.putJson(edit_url,atom_json,function(resp) {
									Dase.addClass(Dase.$('p_'+href),'hide');
								},Dase.user.eid,Dase.user.htpasswd);
							}
						}
					},Dase.user.eid,Dase.user.htpasswd);
				}
				return false;
			}
		}
	}
}

Dase.initSetParent = function(controls) {
	var mform = Dase.$('ajaxFormHolder');
	var links = controls.getElementsByTagName('a');
	//find the "link to parent links
	for (var i=0;i<links.length;i++) {
		if ('setParentLink' == links[i].className) {
			links[i].onclick = function() {
				var parent_type_url = this.href;
				Dase.addClass(Dase.$('adminPageControls'),'hide');
				Dase.removeClass(Dase.$('pageReloader'),'hide');
				//init the page reloading 'close' link
				Dase.$('pageReloaderLink').onclick = function() {
					Dase.pageReload();
					return false;
				}
				//display the form
				if (Dase.toggle(mform)) {
					mform.innerHTML = '<h1 class="loading">Loading...</h1>';
					//retrieve the items of the parent type
					Dase.getJSON(parent_type_url,function(pt_json) {
						var data = {};
						data.items=pt_json.items.sort(Dase.sortByTitle);
						data.count=pt_json.items.length;
						data.name=pt_json.name;
						var h = new Dase.htmlbuilder;
						h.add('h1','attach to '+data.name);
						h.add('ul',{'id':'currentLinks'});
						var form = h.add('form',{'id':'setParentForm'});
						var sel = form.add('select',{'name':'url'});
						sel.add('option',null,'select one of ('+data.count+')');
						for (var i=0;i<data.items.length;i++) {
							var item = data.items[i];
							sel.add('option',{'value':item.url},item.title);
						}
						form.add('input',{'type':'submit','value':'create link','id':'createLink'});
						form.add('span',null,' ');
						form.add('input',{'type':'submit','value':'cancel','id':'cancelLink'});
						form.add('span',{'id':'updateMsg'});
						form.attach(mform);
						var parents = [];
						var edit_url = Dase.atompub.getJsonEditLink();
						Dase.getJSON(edit_url,function(atom_json){
							var pForm = Dase.$('setParentForm');
							Dase.initSetParentForm(pForm,atom_json);
						},Dase.user.eid,Dase.user.htpasswd);
					});
					return false;
				}
			}
		}
	}
}

Dase.initSetParentForm = function(form,atom_json) {
	Dase.$('cancelLink').onclick = function() {
		Dase.addClass(Dase.$('ajaxFormHolder'),'hide');
		Dase.removeClass(Dase.$('adminPageControls'),'hide');
		Dase.addClass(Dase.$('pageReloader'),'hide');
		form.onsubmit = function() {
			return false;
		}
		return false;
	}; 
	form.onsubmit = function() {
		Dase.$('updateMsg').innerHTML = " creating parent link...";
		var link = {};
		link.href = form.url.options[form.url.selectedIndex].value;
		link.rel = 'http://daseproject.org/relation/parent';
		atom_json.link[atom_json.link.length] = link;
		Dase.atompub.putJson(Dase.atompub.getEditLink(),atom_json,function(resp) {
			Dase.pageReload();
		},Dase.user.eid,Dase.user.htpasswd);
		return false;
	};
};

Dase.initAddAnnotation = function() {
	var tog = Dase.$('annotationToggle');
	if (!tog) return;
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
							}
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
				Dase.getJSON(this.href, function(atts){
					h = new Dase.htmlbuilder;
					h.add('h1',null,'Add Metadata');
					var form = h.add('form',{'action':'sss','method':'get','id':'getInputForm'});
					var sel = form.add('select',{'name':'att_ascii_id'});
					sel.add('option',{'value':''},'select an attribute');
					for (var i=0;i<atts.length;i++) {
						var att = atts[i];
						sel.add('option',{'value':att.href},att.attribute_name);
					}
					h.add('div',{'id':'addMetadataFormTarget'});
				h.attach(mform);
				var getForm = Dase.$('getInputForm');
				Dase.initGetInputForm(getForm);
			});
		}
		return false;
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
	//var url = Dase.base_href+'attribute/'+coll+'/'+this.options[this.selectedIndex].value+'.json';
	//what if the attribute had it's own complete url in the original json?
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
			var content_headers = {
				'Content-Type':'application/x-www-form-urlencoded'
			}
			Dase.loadingMsg(true);
			Dase.ajax(this.action,'post',function() { 
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
