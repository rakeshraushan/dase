Dase.updateDefinedValues = function(json) {
	var data = { 
		'defined': json.defined 
	};
	Dase.createDefinedInputSample(json,Dase.$('defined_values_sample'));
	var inp = Dase.$('defined_values_input');
	inp.value = '';
	inp.rows = json.count;
	for (var i=0;i<json.defined.length;i++) {
		var v = json.defined[i];
		inp.value += v+"\n";
	}
};

Dase.createDefinedInputSample = function(json,target) {
	var h;

	if ('select' == json.input) {
		h = new Dase.htmlbuilder('select');
		for (var i=0;i<json.defined.length;i++) {
			var v = json.defined[i];
			h.add('option',null,v);
		}
	}

	if ('checkbox' == json.input || 'radio' == json.input) {
		h = new Dase.htmlbuilder;
		for (var i=0;i<json.defined.length;i++) {
			var v = json.defined[i];
			p = h.add('p');
			p.add('input',{'type':json.input,'name':'sample'});
			p.add('span',null,' '+v);
		}
	}

	h.attach(target);
};

Dase.setDefinedValues = function(form) {
	Dase.ajax(form.action,'get',function(resp) {
		var json = JSON.parse(resp);
		Dase.createDefinedInputSample(json,Dase.$('defined_values_sample'));
		var inp = Dase.$('defined_values_input');
		inp.value = '';
		inp.rows = json.count;
		for (var
		i=0;i<json.defined.length;i++) {
			var v = json.defined[i];
			inp.value += v+"\n";
		}
	});
};

Dase.pageInit = function() {
	var del = Dase.$('deleteAtt');
	if (del) {
		del.onclick = function()
		{
			return confirm('are you sure?');
		}
	}

	var form_toggle = Dase.$('toggleAttributeEditForm');
	if (form_toggle) {
		form_toggle.onclick = function() {
			Dase.toggle(Dase.$('editAttribute'));
			return false;
		};
	}
	var def_form = Dase.$('defined_values_form');
	if (def_form) {
		Dase.setDefinedValues(def_form);
		def_form.onsubmit = function() {
			Dase.ajax(def_form.action,'put',function(resp) {
				var jsonObj = JSON.parse(resp);
				Dase.updateDefinedValues(jsonObj);
			},this.defined_values_input.value);
			return false;
		};
	}
};
