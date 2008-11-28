Dase.json = {};

Dase.pageInit = function() {
	JSONeditor.start('tree','jform',false,true)
	Dase.json.initForm();
};

Dase.json.initForm = function() {
	form = Dase.$('daseJsonForm');
	form.docs.onchange = function() {
		var url = this.options[this.selectedIndex].value;
		Dase.ajax(url,'get',function(resp) {
			Dase.$('jvalue').value = resp;
		});
		//Dase.$('jvalue').value = JSONeditor.treeBuilder.JSONstring.make(JSONeditor.treeBuilder.json);
		return false;
	}
	form.onsubmit = function() {
		var content_headers = {
			'Content-Type':'application/json',
			'Slug':this.title.value
		}
		var json_doc = JSONeditor.treeBuilder.JSONstring.make(JSONeditor.treeBuilder.json);
		Dase.ajax(this.action,'post',function(resp) {
			alert('success');
			Dase.pageReload();
		},json_doc,Dase.user.eid,Dase.user.htpasswd,content_headers,function(error)
		{
			alert(error);
		});
		return false;
	}
};

