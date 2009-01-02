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
};

Dase.demo.processPut = function(form) {
	var headers = {
		'Content-Type':'application/atom+xml;type=entry'
	}
	Dase.ajax(form.path.value,'put',function(resp) { 
		form.formText.value = resp;
	},form.formText.value,Dase.user.eid,Dase.user.htpasswd,headers,function(error) {
		form.formText.value = error;
	}); 
};
