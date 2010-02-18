Dase.scheme = {};

Dase.pageInitUser = function(eid) {
	Dase.scheme.initDeleteScheme();
};

Dase.scheme.initDeleteScheme = function() {
	var table = Dase.$('schemes');
	if (!table) return;
	var links = table.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		if ('delete' == links[i].className) {
			links[i].onclick = function() {
				if (confirm('are you sure?')) {
					Dase.ajax(this.href,'delete',function(resp) {
						Dase.pageReload(resp);
					},null,Dase.user.eid,Dase.user.htpasswd,null,function(resp) {
						//error function
						alert(resp);
					});
				}
				return false;
			}
		}
	}
};

