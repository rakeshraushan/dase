Dase.pageInit = function() {
	var form = Dase.$('ingestCollectionForm');
	var msg = Dase.$('msg');
	form.onsubmit = function() {
		var serform = Dase.form.serialize(this);
		var form = this;
		msg.innerHTML = "Retrieving Data...";
		var interval = setTimeout(function() {
			msg.innerHTML = "Retrieving Data...this may take a moment";
		}, 1000);
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(Dase.base_href+'/manage/ingest/checker','post',function(resp) {
			clearTimeout(interval);
			var parts = resp.split('|');
			if ('ok' == parts[0]) {
				msg.innerHTML = parts[2]+' is a valid DASe Collection with '+parts[1]+' items';
				Dase.removeClass(Dase.$('next'),'hide');
				Dase.ajax(Dase.base_href+'/manage/ingester','post',function(resp) {
					msg.innerHTML = resp;
				},serform,null,null,content_headers);
			} else {
				msg.innerHTML = '';
				alert('sorry, that is not a valid DASe Collection');
			}
		},serform,null,null,content_headers);
		return false;
	}
};
