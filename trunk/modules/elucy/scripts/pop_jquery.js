if (!Efossils) {
	var Efossils = {};
}

jQuery(function() {
		Efossils.initPop();
		});

Efossils.initPop = function() {
	$('#popouts/div.pop').hide();
	$('div.sites/ul/li').hover(function() {
			jQuery("#" + jQuery(this).attr('class')).show();
			},
			function() {
			jQuery("#" + jQuery(this).attr('class')).hide();
			});
}
