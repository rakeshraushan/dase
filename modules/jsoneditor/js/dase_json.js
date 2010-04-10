Dase.json = {};

Dase.pageInit = function() {
	JSONeditor.start('tree','jform',false,true)
};

Dase.pageInitUser = function(eid) {
	Dase.json.initForms();
}

Dase.json.initForms = function() {
	var user = Dase.user.eid;
	var pass = Dase.user.htpasswd;
	form = Dase.$('daseJsonSaveForm');
	form2 = Dase.$('daseJsonSaveAsForm');
	form3 = Dase.$('daseJsonDeleteForm');
	form.reset();
	form2.reset();
	form.docs.onchange = function() {
		var url = this.options[this.selectedIndex].value;
		var title = this.options[this.selectedIndex].text;
		form.action = url;
		form3.action = url.slice(0,-8);
		Dase.$('documentTitle').innerHTML = title;
		form3.delete.value = 'delete '+title;
		Dase.ajax(url,'get',function(resp) {
			Dase.$('jvalue').value = resp;
		});
		return false;
	}
	form.onsubmit = function() {
		var content_headers = {
			'Content-Type':'application/json',
		}
		var json_doc = JSONeditor.treeBuilder.JSONstring.make(JSONeditor.treeBuilder.json);
		Dase.ajax(this.action,this.method,function(resp) {
			if ('added content' == resp) {
				alert('updated '+Dase.$('documentTitle').innerHTML);
			}
		},json_doc,user,pass,content_headers,function(error)
		{
			alert(error);
		});
		return false;
	}
	form2.onsubmit = function() {
		if (!this.title.value) {
			alert('you must provide a title');
			return false;
		}
		var content_headers = {
			'Content-Type':'application/json',
			'Slug':this.title.value
		}
		var json_doc = JSONeditor.treeBuilder.JSONstring.make(JSONeditor.treeBuilder.json);
		Dase.ajax(this.action,this.method,function(resp) {
			form2.reset();
			alert('success');
			Dase.pageReload();
		},json_doc,user,pass,content_headers,function(error)
		{
			alert(error);
		});
		return false;
	}
	form3.onsubmit = function() {
		if (!confirm('are you sure?')) {
			return false;
		}
		Dase.ajax(this.action,this.method,function(resp) {
			form3.reset();
			alert('deleted!');
			Dase.pageReload();
		},null,user,pass,null,function(error)
		{
			alert(error);
		});
		return false;
	}
};

