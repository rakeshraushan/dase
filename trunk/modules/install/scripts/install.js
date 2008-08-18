Dase.install = {};

Dase.install.initCheckForm = function() {
	var form = Dase.$('check_form');

	Dase.$('repos_check_button').onclick = function() {
		Dase.$('init_db').className = 'hide';
		Dase.$('local_config').className = 'hide';
		Dase.$('init_db_msg').innerHTML = '';
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(Dase.base_href+'pathchecker','post',function(resp) { 
			parts = resp.split('|');
			Dase.$('path_to_media_msg').className = parts[0];
			Dase.$('path_to_media_msg').innerHTML = parts[1];
			Dase.$('graveyard_msg').className = parts[2];
			Dase.$('graveyard_msg').innerHTML = parts[3];
		},Dase.form.serialize(form),null,null,content_headers); 
		return false;
	};

	//deal with database type and sqlite db path input
	var type_select = form.db_type;
	var db_path = Dase.$('db_path');
	if ('sqlite' != type_select.options[type_select.options.selectedIndex].value) {
		db_path.className = 'hide';
	}
	form.db_type.onchange = function() {
		Dase.$('db_msg').innerHTML = '';
		Dase.$('init_db').className = 'hide';
		if ('sqlite' != type_select.options[type_select.options.selectedIndex].value) {
			db_path.className = 'hide';
		} else {
			db_path.className = '';
		}
	}
	Dase.$('db_check_button').onclick = function() {
		Dase.$('init_db').className = 'hide';
		Dase.$('local_config').className = 'hide';
		Dase.$('init_db_msg').innerHTML = '';
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
				Dase.$('init_db').className = 'hide';
				Dase.$('local_config').className = 'hide';
			}
			if ('ready' == parts[0]) {
				db_msg.className = 'msg_ready';
				Dase.removeClass(Dase.$('init_db'),'hide');
			}
		},Dase.form.serialize(form),null,null,content_headers); 
		return false;
	};
	Dase.$('save_settings_button').onclick = function() {
		if ('' == Dase.util.trim(form.eid.value)) {
			alert('please enter username');
			return false;
		}
		if ('' == Dase.util.trim(form.password.value)) {
			alert('please enter password');
			return false;
		}
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(Dase.base_href+'savesettings','post',function(resp) { 
			parts = resp.split('|');
			var db_msg = Dase.$('init_db_msg');
			db_msg.innerHTML = parts[1];
			if ('ok' == parts[0]) {
				db_msg.className = 'msg_ok';
			}
			if ('no' == parts[0]) {
				db_msg.className = 'msg_no';
				Dase.$('local_config').className = 'hide';
				Dase.$('db_msg').innerHTML = '';
			}
			if ('display' == parts[0]) {
				db_msg.className = 'msg_no';
				Dase.$('local_config_txt').value = parts[2];
				Dase.removeClass(Dase.$('local_config_txt'),'hide');
				Dase.$('save_settings_button').value = 'confirm settings';
			}
			if ('ready' == parts[0]) {
				db_msg.className = 'msg_ok';
				Dase.removeClass(Dase.$('init_db_button'),'hide');
			}
		},Dase.form.serialize(form),null,null,content_headers); 
		return false;
	};
	Dase.$('init_db_button').onclick = function() {
		var db_msg = Dase.$('init_db_msg');
		db_msg.innerHTML = "<blink>initializing database and importing test collection</blink>";
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(Dase.base_href+'dbinit','post',function(resp) { 
			parts = resp.split('|');
			db_msg.innerHTML = parts[1];
			if ('ok' == parts[0]) {
				db_msg.className = 'msg_ok';
				Dase.removeClass(Dase.$('completed'),'hide');
			}
			if ('no' == parts[0]) {
				db_msg.className = 'msg_no';
				Dase.$('local_config').className = 'hide';
				Dase.$('db_msg').innerHTML = '';
			}
			if ('nowrite' == parts[0]) {
				db_msg.className = 'msg_no';
				Dase.$('local_config_txt').value = parts[2];
			}
		},Dase.form.serialize(form),null,null,content_headers); 
		return false;
	};
};

Dase.pageInit = function() {
	Dase.install.initCheckForm();
}

