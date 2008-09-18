Dase.forms = {};

Dase.forms.initForm = function() {
	delete_links = document.getElementsByTagName('a');
	for (var i=0;i<delete_links.length;i++) {
		if ('delete' == delete_links[i].className) {
			delete_links[i].onclick = function() {
				if (!confirm('Are you Sure?')) return;
				Dase.ajax(this.href,'DELETE',function(resp) {
					//check code
					window.location.href = window.location.href 
				},null,Dase.user.eid,Dase.user.htpasswd);
				return false;
			}
		}
	}
};

Dase.pageInit = function() {
	Dase.forms.initForm();
}

