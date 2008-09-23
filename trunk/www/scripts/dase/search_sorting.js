Dase.pageInit = function() {
	var url_elem = Dase.$('attributes_json_url');
	var div = Dase.$('sortByAttFormDiv');
	if (!url_elem || !div) return;
	var url = url_elem.innerHTML;
	Dase.getJSON(url,function(json){
		var data = { 'atts': json };
		var templateObj = TrimPath.parseDOMTemplate("attributes_jst");
		div.innerHTML = templateObj.process(data);
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

