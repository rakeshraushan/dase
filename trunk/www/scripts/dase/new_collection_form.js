Dase.pageInit = function() {
	var form = Dase.$('newCollection');
	form.onsubmit = function() {
		data = {};
		data.collection_name = form.collection_name.value;
		data.eid = Dase.user.eid;
		var ascii_id = form.collection_name.value.replace(/(collection|archive)/i,'').replace(/ /gi,"_").replace(/(__|_$)/g,'').toLowerCase();
		//make sure ascii_id has only 'word' characters
		if (ascii_id.search(/[\W]/) >= 0) {
			alert(ascii_id+' includes illegal characters');
			return false;
		}
		data.id = Dase.base_href+'collection/'+ascii_id; 
		data.date = Dase.atompub.getDate();
		data.ascii_id = ascii_id; 
		var templateObj = TrimPath.parseDOMTemplate("atom_jst");
		var atom = Dase.trim(templateObj.process(data));
		var content_headers = {
			'Content-Type':'application/atom+xml;type=entry; charset=UTF-8;',
			//'Content-Type':'application/json',
			'Content-MD5':hex_md5(atom),
		}
		if (confirm("You are about to create collection with ascii id\n\n"+ascii_id+"\n\nOK?")) {
			Dase.ajax(Dase.base_href+'collections','post',function(resp) {
				var loc = resp;
				window.location = loc.replace(/\.atom/,"");
			},atom,Dase.user.eid,Dase.user.htpasswd,content_headers, function(resp) {
				//error function
				alert(resp);
			});
		}
		return false;
	}
};
