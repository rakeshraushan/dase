Dase.setTypeAtts = function(form) {
	form.reset();
	var d = new Date();
	//bust cache for frequent updating
	var url = form.action+'?cb='+d.getTime();
	Dase.ajax(url,'get',function(resp) {
		var json = JSON.parse(resp);
		var data = { 
			'atts': json 
		};
		var templateObj = TrimPath.parseDOMTemplate("type_atts_jst");
		Dase.$('deletableAtts').innerHTML = templateObj.process(data);
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
		var json = JSON.parse(resp);
		var data = { 
			'rels': json 
		};
		var templateObj = TrimPath.parseDOMTemplate("type_rels_jst");
		Dase.$('deletableTypes').innerHTML = templateObj.process(data);
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
			var data = {'atom':jsa};
			var templateObj = TrimPath.parseDOMTemplate("atom_jst");
			atom_xml = Dase.trim(templateObj.process(data));
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
