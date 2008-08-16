Dase.install = {};

Dase.install.initCheckDb = function() {
	var form = Dase.$('db_form');
	form.onsubmit = function() {
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(this.action,'post',function(resp) { 
			parts = resp.split('|');
			var msg = Dase.$('msg');
			msg.innerHTML = parts[1];
			if ('ok' == parts[0]) {
				msg.className = 'msg_ok';
			} else {
				msg.className = 'msg_not_ok';
			}
		},Dase.form.serialize(this),null,null,content_headers); 
		return false;
	};
};

Dase.pageInit = function() {
	Dase.install.initCheckDb();
}

