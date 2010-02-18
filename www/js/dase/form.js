Dase.form = {};

Dase.form.serialize = function(form) {
	var element_handlers = {
		'input': function(el) { return el.type; },
		'select': function(el) { return 'select'; },
		'textarea': function(el) { return 'textarea'; }
	}
	var type_handlers = {
		'text': function(el) { return el.value; },
		'hidden' : function(el) { return el.value; },
		'textarea': function(el) { return el.value; },
		'select': function(el) { 
			return el.options[el.options.selectedIndex].value;
		},
		'radio': function(el) { return el.checked ? el.value : null; },
		'checkbox': function(el) { return el.checked ? el.value : null; }
	}
	var params = {};
	var eList = form.getElementsByTagName('*');
	for(var i=0;i<eList.length;i++) {
		n = eList[i];
		if(n.nodeType && n.nodeType == 1) {
			if(n.tagName && n.name && element_handlers[n.tagName.toLowerCase()] && type_handlers[element_handlers[n.tagName.toLowerCase()](n)]) {
				if(params[n.name] && !(params[n.name] instanceof Array)) {
					params[n.name] = [params[n.name]];
				}

				if(params[n.name]) {
					params[n.name].push(type_handlers[element_handlers[n.tagName.toLowerCase()](n)](n));
				} else {
					params[n.name] = type_handlers[element_handlers[n.tagName.toLowerCase()](n)](n);
				}
			}
		}
	}
	return Dase.form.postify(params);
};

Dase.form.postify =  function(obj) {
	var params = [];
	for(k in obj) {
		if(obj[k] instanceof Array) {
			var multivalue = [];
			for(kk in obj[k]) {
				if(obj[k][kk] != null) params.push(encodeURIComponent(k.toString())+'='+encodeURIComponent(obj[k][kk].toString()));
			}
		} else {
			if(obj[k] != null) params.push(encodeURIComponent(k.toString())+'='+encodeURIComponent(obj[k].toString()));
		}
	}
	return params.join('&');
};
