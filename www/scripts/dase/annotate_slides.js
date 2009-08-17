Dase.pageInitUser = function() {
	var table = Dase.$('annotate');
	var links = table.getElementsByTagName('a');
	for (var i = 0; i < links.length; i++) {
		if ('toggleForm' == links[i].className) {
			links[i].onclick = function() {
				var id = this.id;
				var form = Dase.$(id+'_form');
				if (form) {
					Dase.toggle(form);
				}
				var annotation = Dase.$(id+'_annotation');
				if (annotation) {
					Dase.toggle(annotation);
				}
				return false;
			}
		}
	}
	var forms = table.getElementsByTagName('form');
	for (var i = 0; i < forms.length; i++) {
		forms[i].onsubmit = function() {
			var my_form = this;
			var my_annot = Dase.$(this.id.replace('_form','_annotation'));
			annotation_url = this.action;
			Dase.ajax(annotation_url,'put',function(resp){
				my_annot.innerHTML = resp;
				Dase.toggle(my_form);
				Dase.toggle(my_annot);
			},this.annotation.value,Dase.user.eid,Dase.user.htpasswd);
			return false;
		}
	}
};

