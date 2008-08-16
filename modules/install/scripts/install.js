Dase.install = {};

Dase.install.initCheckDb = function() {
	var form = Dase.$('check_form');
	var type_select = form.db_type;
	var db_path = Dase.$('db_path');
	if ('sqlite' != type_select.options[type_select.options.selectedIndex].value) {
		db_path.className = 'hide';
	}
	form.db_type.onchange = function() {
		if ('sqlite' != type_select.options[type_select.options.selectedIndex].value) {
			db_path.className = 'hide';
		} else {
			db_path.className = '';
		}
	}
	var dbc = Dase.$('db_check_button');
	dbc.onclick = function() {
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(Dase.base_href+'dbchecker','post',function(resp) { 
			parts = resp.split('|');
			var db_msg = Dase.$('db_msg');
			db_msg.innerHTML = parts[1];
			if ('ok' == parts[0]) {
				db_msg.className = 'msg_ok';
			}
			if ('no' == parts[0]) {
				db_msg.className = 'msg_no';
			}
			if ('ready' == parts[0]) {
				db_msg.className = 'msg_ready';
				Dase.removeClass(Dase.$('init_db'),'hide');
			}
		},Dase.form.serialize(form),null,null,content_headers); 
		return false;
	};
};

Dase.pageInit = function() {
	Dase.install.initCheckDb();
}

