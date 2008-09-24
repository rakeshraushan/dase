Dase.install = {};

Dase.install.resetErrors = function() {
	var spans = document.getElementsByTagName('span');
	for (var i=0;i<spans.length;i++) {
		if (spans[i].className == 'error') {
			spans[i].innerHTML = '';
		}
	}
	Dase.$('local_config_txt').className = 'hide';
};

Dase.pageInit = function() {
	var form = Dase.$('configForm');
	//deal with database type and sqlite db path input
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
	Dase.$('config_check_button').onclick = function() {
		var myform = Dase.$('configForm');
		Dase.install.resetErrors();
		data = Dase.form.serialize(myform);
		if ('' == myform.eid.value) {
			Dase.$('eid_msg').innerHTML = 'username is required';
			return false;
		}
		if ('' == myform.password.value) {
			Dase.$('password_msg').innerHTML = 'password is required';
			return false;
		}
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(Dase.base_href+'modules/install/config_checker','post',function(resp) { 
				json = JSON.parse(resp);
				if (!json.path) {
				Dase.$('path_to_media_msg').innerHTML = 'path must be writable by web server';
				}
				if (!json.db) {
				Dase.$('db_msg').innerHTML = 'could not connect to db';
				}
				if (json.path && json.db && json.config) {
				Dase.$('config_check_msg').innerHTML = 'success!';
				Dase.$('local_config_msg').innerHTML = 'copy the following to '+json.local_config_path+':';
				Dase.$('local_config_txt').value = json.config;
				Dase.removeClass(Dase.$('local_config_txt'),'hide');
				}
				return;
		},data,null,null,content_headers); 
		return false;
	};
};
