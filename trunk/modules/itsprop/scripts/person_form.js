Dase.pageInit = function() {
	Dase.initDept();
}

Dase.sortByTitle = function(a,b) {
	var x = a.title.toLowerCase();
	var y = b.title.toLowerCase();
	if ( x < y ) return -1;
	if ( x > y ) return  1;
	return  0;
}

Dase.initDept = function() {
	var url = 'http://dev.laits.utexas.edu/itsprop/new/item_type/itsprop/department/items.json'
	Dase.getJSON(url,function(json){
		var data = {};
		data.depts = json.sort(Dase.sortByTitle);
		var templateObj = TrimPath.parseDOMTemplate("select_jst");
		//display the form
		Dase.$('select_dept').innerHTML = templateObj.process(data);
		//Dase.initPersonForm();
	}
	,null,null,'pkeane','itsprop8');
}

Dase.initPersonForm = function() {
	var edit_url = Dase.atompub.getJsonEditLink();
	Dase.getJSON(edit_url,function(atom_json){
		alert(JSON.stringify(atom_jsom));
	},Dase.user.eid,Dase.user.htpasswd);
	var form = Dase.$('personForm');
	form.onsubmit = function() {
		var link = {};
		link.href = form.department.options[form.department.selectedIndex].value;
		link.rel = 'http://daseproject.org/relation/parent';
		atom_json.link[atom_json.link.length] = link;
		Dase.atompub.putJson(Dase.atompub.getEditLink(),atom_json,function(resp) {
			Dase.pageReload();
		},Dase.user.eid,Dase.user.htpasswd);
		return false;
		alert('dd');
		return false;
	}
}
