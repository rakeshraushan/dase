Dase.pageInit = function() {
	var url_elem = Dase.$('attributes_json_url');
	var attsform = Dase.$('attributesForm');
	if (!url_elem || !attsform) return;
	var url = url_elem.innerHTML;
	Dase.getJSON(url,function(json){
		var data = { 'atts': json };
		var templateObj = TrimPath.parseDOMTemplate("attributes_jst");
		attsform.innerHTML = templateObj.process(data);
	});
};

