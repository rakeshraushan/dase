Dase.setTypeAtts = function(form) {
	Dase.ajax(form.action,'get',function(resp) {
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
};
