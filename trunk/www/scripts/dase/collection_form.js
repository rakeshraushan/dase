Dase.pageInitUser = function() {
	var form = Dase.$('newCollection');
	form.onsubmit = function() {
		data = {};
		data.title = form.collection_name.value;
		data.author_name = Dase.user.eid;
		var ascii_id = form.collection_name.value.replace(/(collection|archive)/i,'').replace(/ /gi,"_").replace(/(__|_$)/g,'').toLowerCase();
		//make sure ascii_id has only 'word' characters
		if (ascii_id.search(/[\W]/) >= 0) {
			alert(ascii_id+' includes illegal characters');
			return false;
		}
		data.id = Dase.base_href+'collection/'+ascii_id; 
		data.updated = Dase.atompub.getDate();
		data.content = {};
		data.content.text = ascii_id; 
		data.entrytype = 'collection';
		data.category = [];
		data.link = [];
		var atom = Dase.atompub.json2atom(data);
		var content_headers = {
			'Content-Type':'application/atom+xml;type=entry; charset=UTF-8;',
			//'Content-Type':'application/json',
			'Content-MD5':hex_md5(atom),
		}
		if (confirm("You are about to create collection with ascii id\n\n"+ascii_id+"\n\nOK?")) {
			Dase.ajax(Dase.base_href+'collections','post',function(resp) {
				alert(resp);
				var loc = resp;
				//window.location = loc.replace(/\.atom/,"");
			},atom,Dase.user.eid,Dase.user.htpasswd,content_headers, function(resp) {
				//error function
				alert(resp);
			});
		}
		return false;
	}
};
