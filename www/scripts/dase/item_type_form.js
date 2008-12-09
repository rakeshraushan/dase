Dase.setTypeAtts = function(form) {
	Dase.ajax(form.action,'get',function(resp) {
		var json = JSON.parse(resp);
		var data = { 
			'atts': json 
		};
		var templateObj = TrimPath.parseDOMTemplate("type_atts_jst");
		Dase.$('deletable').innerHTML = templateObj.process(data);
		links = Dase.$('deletable').getElementsByTagName('a');
		for (var i=0;i<links.length;i++) {
			ln = links[i];
			if (Dase.hasClass(ln,'delete')) {
				ln.onclick = function() {
					if (confirm('are you sure?')) {
						Dase.ajax(ln.href,'delete',function(resp) {
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
};
