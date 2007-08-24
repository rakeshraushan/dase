if (!Vrc) {
	var Vrc = {};
}

jQuery(function() {
		Vrc.initPop();
		});

Vrc.initPop = function() {
	$('#popouts/div.pop').hide();
	$('div.sites/ul/li').hover(function() {
			jQuery("#" + jQuery(this).attr('class')).show();
			},
			function() {
			jQuery("#" + jQuery(this).attr('class')).hide();
			});
}
