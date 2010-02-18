Dase.demo = {};

Dase.pageInit = function() {
	Dase.demo.initDemo();
}

//if page needs to know user 
Dase.pageInitUser = function(eid) {
};


Dase.demo.initDemo = function() {
	form = Dase.$('demoForm');
	if (!form) return;
	form.onsubmit = function() {
		return false;
	}
	Dase.$('submitGet').onclick = function() {
		Dase.demo.processGet(form);
	}
	Dase.$('submitDelete').onclick = function() {
		alert('delete not enabled yet');
	}
	Dase.$('submitPut').onclick = function() {
		Dase.demo.processPut(form);
	}
	Dase.$('submitPost').onclick = function() {
		alert('post not enabled yet');
	}
}

Dase.demo.processGet = function(form) {
	form.formText.value = 'loading...';
	Dase.ajax(form.path.value,'get',function(resp) { 
		form.formText.value = resp;
	},null,null,null,null,function(error) {
		form.formText.value = error;
	}); 
	var json_url = form.path.value.replace(/\.atom/,'.json');
	Dase.getJSON(json_url,function(json) {
		if (json.id) {
		Dase.$('atomDisplay').innerHTML = Dase.demo.atom_display(json);
	}
},Dase.user.eid,Dase.user.htpasswd);
};

Dase.demo.processPut = function(form) {
	Dase.$('atomDisplay').innerHTML = 'GET item to see display';
	var headers = {
		'Content-Type':'application/atom+xml;type=entry'
	}
	Dase.ajax(form.path.value,'put',function(resp) { 
		form.formText.value = resp;
	},form.formText.value,Dase.user.eid,Dase.user.htpasswd,headers,function(error) {
		form.formText.value = error;
	}); 
};


Dase.demo.atom_display = function(atom) {
	h = new Dase.htmlbuilder;
	h.add('h2',null,'elements');
	var table = h.add('table',{'class':'atom list'});
	var tr = table.add('tr');
	var th = tr.add('th',null,'id');
	var td = tr.add('td',null,atom.id);
	tr = table.add('tr');
	th = tr.add('th',null,'title');
	td = tr.add('td',null,atom.title);
	tr = table.add('tr');
	th = tr.add('th',null,'author/name');
	td = tr.add('td',null,atom.author_name);
	tr = table.add('tr');
	th = tr.add('th',null,'summary');
	td = tr.add('td',null,atom.summary);
	tr = table.add('tr');
	th = tr.add('th',null,'rights');
	td = tr.add('td',null,atom.rights);
	tr = table.add('tr');
	th = tr.add('th',null,'updated');
	td = tr.add('td',null,atom.updated);
	tr = table.add('tr');
	th = tr.add('th',null,'content@type');
	td = tr.add('td',null,atom.content.type);
	tr = table.add('tr');
	th = tr.add('th',null,'content');
	td = tr.add('td',null,atom.content.text);
	tr = table.add('tr');
	th = tr.add('th',null,'entrytype');
	td = tr.add('td',null,atom.entrytype);
	h.add('h2',null,'categories');
	table = h.add('table',{'class':'atom'});
	tr = table.add('tr');
	th = tr.add('th',null,'term');
	th = tr.add('th',null,'scheme');
	th = tr.add('th',null,'label');
	th = tr.add('th',null,'value');
	for (var i=0;i<atom.category.length;i++) {
		var cat = atom.category[i];
		tr = table.add('tr');
		td = tr.add('td',null,cat.term);
		td = tr.add('td',null,cat.scheme);
		td = tr.add('td',null,cat.label);
		td = tr.add('td',null,cat.value);
	}
	h.add('h2',null,'links');
	table = h.add('table',{'class':'atom'});
	tr = table.add('tr');
	th = tr.add('th',null,'rel');
	th = tr.add('th',null,'href');
	th = tr.add('th',null,'type');
	th = tr.add('th',null,'title');
	th = tr.add('th',null,'length');
	for (var i=0;i<atom.link.length;i++) {
		var ln = atom.link[i];
		tr = table.add('tr');
		td = tr.add('td',null,ln.rel);
		td = tr.add('td',null,ln.href);
		td = tr.add('td',null,ln.type);
		td = tr.add('td',null,ln.title);
		td = tr.add('td',null,ln.lengtd);
	}
	return h.getString();
}
