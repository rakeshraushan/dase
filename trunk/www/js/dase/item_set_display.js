Dase.pageInit = function() {
	Dase.initSlideshowLink();
	Dase.initBulkEditLink();
	Dase.getItemUniques();
	if (Dase.initSearchSorting) {
		Dase.initSearchSorting();
	}
//	Dase.initZoomer();
}

Dase.getItemUniques = function() {
	Dase.set_uniques = [];
	var itemSet =Dase.$('itemSet');
	if (itemSet) {
		var set = itemSet.getElementsByTagName('input');
		for (var i=0;i<set.length;i++) {
			if ('item_unique[]' == set[i].getAttribute('name')) {
				var uniq = set[i].getAttribute('value');
				Dase.set_uniques[Dase.set_uniques.length] = uniq;
			}
		}
	}
};


Dase.initBulkEditLink = function() {
	var belink = Dase.$('bulkEditor');
	if (!belink) return;
	var mform = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	belink.onclick = function() {
		if (Dase.toggle(mform)) {
			mform.innerHTML = '<h2 class="loading">Loading...</h2>';
			Dase.getJSON(this.href, function(profile){
				Dase.profile = profile;
				h = new Dase.htmlbuilder;
				h.add('h2',null,'Add Metadata');
				//form to select attribute
				var form = h.add('form',{'action':'auto','method':'get','id':'getInputForm'});
				var sel = form.add('select',{'name':'att_ascii_id'});
				sel.add('option',{'value':''},'select an attribute');
				for (var n in profile.attributes) {
					sel.add('option',{'value':n},profile.attributes[n]);
				}

				//target for actual input form
				h.add('div',{'id':'addMetadataFormTarget'});

				//bulk set status
				h.add('h2',null,'Bulk Set Status');
				var form = h.add('form',{'action':Dase.base_href+'item/{item_unique}/status','method':'post','id':'set_status'});
				var sel = form.add('select',{'name':'status'});
				sel.add('option',{'value':''},'select status');
				sel.add('option',{'value':'public'},'public');
				sel.add('option',{'value':'draft'},'draft');
				sel.add('option',{'value':'delete'},'delete');
				sel.add('option',{'value':'archive'},'archive');
				var button = form.add('input',{'type':'submit','value':'set status'});

				//bulk set item type 
				h.add('h2',null,'Bulk Set Item Type');
				var form = h.add('form',{'action':Dase.base_href+'item/{item_unique}/item_type','method':'post','id':'set_item_type'});
				var sel = form.add('select',{'name':'item_type'});
				sel.add('option',{'value':''},'select item type');
				for (var n in profile.item_types) {
					sel.add('option',{'value':n},profile.item_types[n]);
				}
				var button = form.add('input',{'type':'submit','value':'set item type'});

				//bulk delete common
				h.add('h2',null,'Bulk Delete Common Metadata');
				var form = h.add('form',{'action':Dase.base_href+'item/{item_unique}/keyval_remover','method':'post','id':'bulk_delete_common'});
				var sel = form.add('select',{'name':'keyval','id':'common_kv'});
				sel.add('option',{'value':''},'retrieving data...');
				var button = form.add('input',{'type':'submit','value':'delete metadata'});


				//setup
				h.attach(mform);

				Dase.initGetInputForm();

				//could be refactored into ONE function
				Dase.initBulkSetStatus('set_status');
				Dase.initSetInputTypeForm('set_item_type');
				Dase.initBulkDeleteForm();

			},null,'sort=attribute_name');
		}
		return  false;
	};
};

Dase.initBulkSetStatus = function(id) {
	var form = Dase.$(id);
	form.onsubmit = function() {
		var data = jQuery(this).serialize();
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		if (confirm('are you sure you wish to update all items in this set?')) {
			var msg = Dase.$('ajaxMsg');
			msg.innerHTML = 'updating '+Dase.set_uniques.length+" items";
			Dase.toggle(msg);
			Dase.ajaxResponses = 0;
			var url_tmpl = $(this).attr('action');
			for (k in Dase.set_uniques) {
				var uniq = Dase.set_uniques[k];
				var url = url_tmpl.replace('{item_unique}',uniq);
				Dase.ajax(url,'POST',
				function(resp) { 
					Dase.ajaxResponses += 1;
					msg.innerHTML = 'processed '+Dase.ajaxResponses+' items';
					if (Dase.ajaxResponses == Dase.set_uniques.length) {
						Dase.pageReload();
					}
				},data,null,null,content_headers,function(resp) {
					Dase.ajaxResponses += 1;
					//alert('error '+resp);
				});
			}
		}
		return false;
	};
};

Dase.initSetInputTypeForm = function(id) {
	var form = Dase.$(id);
	form.onsubmit = function() {
		var data = jQuery(this).serialize();
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		if (confirm('are you sure you wish to update all items in this set?')) {
			var msg = Dase.$('ajaxMsg');
			msg.innerHTML = 'updating '+Dase.set_uniques.length+" items";
			Dase.toggle(msg);
			Dase.ajaxResponses = 0;
			var url_tmpl = $(this).attr('action');
			for (k in Dase.set_uniques) {
				var uniq = Dase.set_uniques[k];
				var url = url_tmpl.replace('{item_unique}',uniq);
				Dase.ajax(url,'POST',
				function(resp) { 
					Dase.ajaxResponses += 1;
					msg.innerHTML = 'processed '+Dase.ajaxResponses+' items';
					if (Dase.ajaxResponses == Dase.set_uniques.length) {
						Dase.pageReload();
					}
				},data,null,null,content_headers,function(resp) {
					Dase.ajaxResponses += 1;
					//alert('error '+resp);
				});
			}
		}
		return false;
	};
};

Dase.initBulkDeleteForm = function(form) {
	var url = Dase.getLinkByRel('common_keyvals');
	Dase.getJSON(url,function(resp) {
		h = new Dase.htmlbuilder;
		h.add('option',null,'select an attribute/value pair');
		for (n in resp) {
			h.add('option',null,n);
		}
		h.attach(Dase.$('common_kv'));
		var form = Dase.$('bulk_delete_common');
		form.onsubmit = function() {
			var url_tmpl = $(this).attr('action');
			var kv = this.keyval.options[this.keyval.selectedIndex].value;
			if (confirm('are you sure you wish to delete\n\n'+kv+'\n\nin all of all items in this set?')) {
				var doomed = JSON.stringify(resp[kv]);
				Dase.deleteKeyvalFromSetItems(doomed,url_tmpl);
			}
			return false;
		};
	});
};

Dase.deleteKeyvalFromSetItems = function(doomed,url_tmpl) {
	var msg = Dase.$('ajaxMsg');
	msg.innerHTML = 'updating '+Dase.set_uniques.length+" items";
	//msg.innerHTML = '<span id="processing_count"></span> items to process';
	Dase.toggle(msg);
	//Dase.countdown('processing_count',Dase.set_uniques.length,300);
	Dase.ajaxResponses = 0;
	for (k in Dase.set_uniques) {
		var uniq = Dase.set_uniques[k];
		var url = url_tmpl.replace('{item_unique}',uniq);
		Dase.ajax(url,'POST',
		function(resp) { 
			Dase.ajaxResponses += 1;
			msg.innerHTML = 'processed '+Dase.ajaxResponses+' items';
			if (Dase.ajaxResponses == Dase.set_uniques.length) {
				Dase.pageReload();
			}
		},doomed,null,null,null,function(resp) {
			Dase.ajaxResponses += 1;
			//alert('error '+resp);
		});
	}
};

Dase.initGetInputForm = function() {
	var coll = Dase.$('collectionAsciiId').innerHTML;
	var form = Dase.$('getInputForm');
	form.att_ascii_id.onchange = function() { //this is the attribute selector
	var url = Dase.base_href+'attribute/'+coll+'/'+this.options[this.selectedIndex].value+'.json';
	Dase.getJSON(url,function(resp) {
		resp.eid_ascii = Dase.$('tagEid').innerHTML+'/'+Dase.$('tagAsciiId').innerHTML;
		if (!resp.html_input_type) {
			resp.html_input_type = 'text';
		}
		Dase.$('addMetadataFormTarget').innerHTML = Dase.getInputForm(resp);
		//for dynamic quick menu
		var select_autofill = Dase.$('select_autofill');
		if  (select_autofill) {
			select_autofill.onchange = function() {
				Dase.$('autofill_target').value = this.options[this.selectedIndex].value;
			}
		}
		//using jQuery for its form serialization function
		//Dase.$('#addMetadataForm').onsubmit = null;
		jQuery('#addMetadataForm').submit(function() {
			var msg = Dase.$('ajaxMsg');
			msg.innerHTML = 'updating '+Dase.set_uniques.length+" items";
			Dase.toggle(msg);
			//Dase.countdown('processing_count',Dase.set_uniques.length,300);
			var data = jQuery(this).serialize();
			var content_headers = {
				'Content-Type':'application/x-www-form-urlencoded'
			}
			Dase.ajaxResponses = 0;
			for (k in Dase.set_uniques) {
				var uniq = Dase.set_uniques[k];
				var url = Dase.base_href+'item/'+uniq+'/metadata';
				Dase.ajax(url,'POST',
				function(resp) { 
					Dase.ajaxResponses += 1;
					msg.innerHTML = 'processed '+Dase.ajaxResponses+' items';
					if (Dase.ajaxResponses == Dase.set_uniques.length) {
						Dase.pageReload();
					}
				},data,null,null,content_headers,function(resp) {
					Dase.ajaxResponses += 1;
					//alert('error '+resp);
				});
			}
			return false;
		});
	});
	return false;
}
};

Dase.getInputForm = function(resp) {
	var vals = resp.values;
	var form = new Dase.htmlbuilder('form',{'method':'get','id':'addMetadataForm'});
	//posts metadata to tag
	form.set('action','tag/'+resp.eid_ascii);
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
		var sel = form.add('p').add('select',{'name':'value_source','id':'select_autofill'});
		sel.add('option',{'value':''},'select one:');
		//not sure why this broke??
		//for (var i=0;i<vals.length;i++) {
		for (var i in vals) {
			var v = vals[i];
			sel.add('option',{'value':v},v);
		}
		break;
	}
	form.add('input',{'type':'submit','value':'add'});
	return form.getString();
}

Dase.initZoomer = function() {
	var table = Dase.$('itemSet');
	if (!table) return;
	var links = table.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		if ('zoomer' == links[i].className) {
			var z = links[i];
			z.onclick = function() {
				var target = Dase.$('thumb'+this.id.replace(/zoom/,'')); //get img link
				if (target) {
					var thumblink = target.src;
					target.src = this.href;
					this.href = thumblink;
					this.className = 'unzoomer';
					this.innerHTML = '[-]';
					Dase.showElem(target);
					Dase.initZoomer();
				}
				return false;
			}
		}
		if ('unzoomer' == links[i].className) {
			var z = links[i];
			z.onclick = function() {
				var target = Dase.$('thumb'+this.id.replace(/zoom/,'')); //get img link
				if (target) {
					var viewitemlink = target.src;
					target.src = this.href;
					this.href = viewitemlink; 
					this.className = 'zoomer';
					this.innerHTML = '[+]';
					Dase.showElem(target);
					Dase.initZoomer();
				}
				return false;
			}
		}
	}
}

Dase.initSlideshowLink = function() {
	var sslink = Dase.$('startSlideshow');
	if (!sslink) return;
	var json_url = Dase.getJsonUrl();
	if (!json_url) {
		Dase.addClass(sslink,'hide');
		return;
	}
	sslink.onclick = function() {
		Dase.slideshow.start(json_url,Dase.user.eid,Dase.user.htpasswd);
		return false;
	}
}

