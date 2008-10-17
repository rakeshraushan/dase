Dase.pageInit = function() {
	var table = Dase.$('managers');
	if (!table) return;
	var delete_links = table.getElementsByTagName('a');
	for (var i=0;i<delete_links.length;i++) {
		if ('delete manager' == delete_links[i].className) {
			delete_links[i].onclick = function() {
				if (!confirm('delete this manager?')) {
					return false;
				}
				Dase.ajax(this.href,'DELETE',
				function(resp) {
					Dase.pageReload(resp);
				},null);
				return false;
			};
		}
	}
};

