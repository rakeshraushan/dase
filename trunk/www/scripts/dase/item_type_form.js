Dase.setTypeAtts = function(form) {
	form.reset();
	var d = new Date();
	//bust cache for frequent updating
	var url = form.action+'?cb='+d.getTime();
	Dase.ajax(url,'get',function(resp) {
		var atts = JSON.parse(resp);
		h = new Dase.htmlbuilder;
		for (var key in atts) {
			var att = atts[key];
			var li = h.add('li');
			var a = li.add('a');
			a.set('href','manage/'+att.collection_ascii_id+'/attribute/'+att.att_ascii_id);
			a.setText(att.attribute_name);
			a = li.add('a');
			a.set('href','manage/'+att.collection_ascii_id+'/item_type/'+att.item_type_ascii+'/attribute/'+att.att_ascii_id);
			a.set('class','delete');
			a.setText('delete');
		}
		h.attach(Dase.$('deletableAtts'));
		links = Dase.$('deletableAtts').getElementsByTagName('a');
		for (var i=0;i<links.length;i++) {
			ln = links[i];
			if (Dase.hasClass(ln,'delete')) {
				ln.onclick = function() {
					if (confirm('are you sure?')) {
						Dase.ajax(this.href,'delete',function(resp) {
							var atts_form = Dase.$('type_atts_form');
							if (atts_form) {
								Dase.setTypeAtts(atts_form);
							}
						});
						return false;
					}
				};
			}
		}
	});
};

Dase.setTypeRels = function(form) {
	Dase.ajax(form.action,'get',function(resp) {
		var rels = JSON.parse(resp);
		var h = new Dase.htmlbuilder;
		for (var key in rels.parents) {
			var r = rels.parents[key];
			var li = h.add('li');
			var a = li.add('a');
			a.set('href','manage/'+r.collection_ascii_id+'/item_type/'+r.ascii_id);
			a.setText(r.name);
			li.add('span',null,'(parent)');
			a = li.add('a');
			a.set('href','manage/'+r.collection_ascii_id+'/item_type_relation/'+r.relation_id);
			a.set('class','delete');
			a.setText('delete');
			if (r.title) {
				li.add('p',{'class':'relationDesc'},r.title);
			}
		}
		for (var key in rels.children) {
			var r = rels.children[key];
			var li = h.add('li');
			var a = li.add('a');
			a.set('href','manage/'+r.collection_ascii_id+'/item_type/'+r.ascii_id);
			a.setText(r.name);
			li.add('span',null,'(child)');
			a = li.add('a');
			a.set('href','manage/'+r.collection_ascii_id+'/item_type_relation/'+r.relation_id);
			a.set('class','delete');
			a.setText('delete');
			if (r.title) {
				li.add('p',{'class':'relationDesc'},r.title);
			}
		}
		h.attach(Dase.$('deletableTypes'));
		links = Dase.$('deletableTypes').getElementsByTagName('a');
		for (var i=0;i<links.length;i++) {
			ln = links[i];
			if (Dase.hasClass(ln,'modify')) {
				ln.onclick = function() {
					var form = Dase.$(this.id.replace(/link/,'form'));
					form.action = this.href;
					Dase.toggle(form);
					form.onsubmit = function() {
						Dase.ajax(this.action,'post',function(resp) {
							Dase.pageInit();
						},this.title.value);
						return false;
					};
					return false;
				};
			}
			if (Dase.hasClass(ln,'delete')) {
				ln.onclick = function() {
					if (confirm('are you sure?')) {
						Dase.ajax(this.href,'delete',function(resp) {
							var rels_form = Dase.$('type_rels_form');
							if (rels_form) {
								Dase.setTypeRels(rels_form);
							}
						});
						return false;
					}
				};
			}
		}
	});
};

Dase.initCreateAttribute = function() {
	var select = Dase.$('att_select');
	if (!select) return;
	select.onchange = function() {
		if ('new_att_trigger' == this.options[this.selectedIndex].value) {
			var att_name = prompt('type attribute name');
			var cats = [{
				'term':this.className,
				'scheme':'http://daseproject.org/category/parent_item_type'
				}];
				//this can all be cleaned up/compressed
				var jsa = Dase.atom.jsonEntry(att_name,'attribute',cats);
				atom_xml = Dase.atompub.json2atom(jsa);
				post_url = Dase.base_href+'collection/'+this.options[this.selectedIndex].className+'/attributes';
				headers = {
					'Content-Type':'application/atom+xml;type=entry',
				}
				Dase.ajax(post_url,'POST',function(resp) {
					var atts_form = Dase.$('type_atts_form');
					if (atts_form) {
						Dase.setTypeAtts(atts_form);
					}
				},atom_xml,Dase.user.eid,Dase.user.htpasswd,headers);
				return false;
			} else {
				return false;
			}
		};
	};

	Dase.pageInit = function() {
		var del = Dase.$('deleteType');
		if (del) {
			del.onclick = function()
			{
				return confirm('are you sure?');
			}
		}
		var atts_form = Dase.$('type_atts_form');
		if (atts_form) {
			Dase.setTypeAtts(atts_form);
		}
		var rels_form = Dase.$('type_rels_form');
		if (rels_form) {
			Dase.setTypeRels(rels_form);
		}
		Dase.initCreateAttribute()
	};
