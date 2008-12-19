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
}

Dase.demo.processGet = function(form) {
	form.formText.value = 'loading...';
	Dase.ajax(form.action+form.path.value,'get',function(resp) { 
		form.formText.value = resp;
	}); 
}
