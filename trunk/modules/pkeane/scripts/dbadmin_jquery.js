if (!Dbadmin) {
	var Dbadmin = {};
}

jQuery(function() {
		Dbadmin.init();
		});

Dbadmin.init = function() {
	$('ul.tableSet li').find('ul').hide().end().click(function() {
			$('ul',this).toggle();
			return false;
			});
}
