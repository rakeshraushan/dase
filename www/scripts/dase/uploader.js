Dase.pageInitUser = function(eid) {
	var coll = Dase.$('collectionAsciiId').innerHTML;
	Dase.getJSON(Dase.base_href+'user/'+eid+'/'+coll+'/recent.json',function(json){
		var data = {'recent' : json};
		var templateObj = TrimPath.parseDOMTemplate('recent_jst');
		Dase.$('recent').innerHTML = templateObj.process(data);
	});
};

