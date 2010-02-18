Dase.pageInit = function() {
	var form = Dase.$('homeSearchForm');
	form.onsubmit = function() {
		var search_form = this;
		inputs = form.getElementsByTagName('input');
		var prefs = new Array();
		for (var i=0;i<inputs.length;i++) {
			var inp = inputs[i];
			if ('c' == inp.name && inp.checked) {
				prefs[prefs.length] = inp.value;
			}
		}
		var content_headers = {
			'Content-Type':'text/plain'
			//dig, ie haes trailing comma
			//'Content-Type':'text/plain',
		}
		var pref_set = prefs.join('|');
		url = Dase.$('settings-link').href+'/preferred';
		//post preferred collections to dase
		//before submitting form
		Dase.ajax(url,'post',function(resp) {
		//	alert(resp);
		search_form.submit();
		},pref_set,null,null,content_headers);
		return false;
	}
};
