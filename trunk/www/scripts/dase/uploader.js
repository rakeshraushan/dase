Dase.pageInitUser = function(eid) {
	var coll = Dase.$('collectionAsciiId').innerHTML;
	Dase.getJSON(Dase.base_href+'user/'+eid+'/'+coll+'/recent.json',function(recent){
		var h = new Dase.htmlbuilder;
		for (var sernum in recent) {
			var rec = recent[sernum];
			var li = h.add('li');
			var a = li.add('a');
			a.set('href',rec.item_record_href);
			a.add('img',{'src':rec.thumbnail_href});
			var h4 = li.add('h4');
			h4.add('a',{'href':rec.item_record_href},rec.title);
		}
		h.attach(Dase.$('recent'));
	});
};

