Dase.initSearchSorting = function() {
	var url_elem = Dase.$('attributes_json_url');
	var div = Dase.$('sortByAttFormDiv');
	if (!url_elem || !div) return;
	var url = url_elem.innerHTML;
	if (!url) return;
	Dase.getJSON(url,function(atts){
		var h = new Dase.htmlbuilder('form',{'id':'sortByAttForm'});
		h.set('method','get');
		h.set('action','search');
		var select = h.add('select',{'name':'att_ascii_id'});
		select.add('option',{'value':''},'select an attribute');
		for (var i=0;i<atts.length;i++) {
			var att = atts[i];
			select.add('option',{'value':att.ascii_id},att.attribute_name);
		}
		h.add('input',{'type':'submit','value':'sort results'});
		h.attach(div);
		var form = Dase.$('sortByAttForm');
		form.onsubmit = function() {
			var att = this.att_ascii_id.options[this.att_ascii_id.selectedIndex].value;
			var current = window.location.href;
			var pattern = /&sort=[a-z_]*/g;
			var new_loc = current.replace(pattern,'')+'&sort='+att;
			window.location.href = new_loc;
			return false;
		};
	});
};

