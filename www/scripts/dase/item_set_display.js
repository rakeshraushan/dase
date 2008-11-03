Dase.pageInit = function() {
	Dase.initSlideshowLink();
	Dase.initBulkEditLink();
}

Dase.initBulkEditLink = function() {
	var belink = Dase.$('bulkEditor');
	if (!belink) return;
	belink.onclick = function() {
		alert('edit away!');
		return  false;
	}
}

Dase.initSlideshowLink = function() {
	var sslink = Dase.$('startSlideshow');
	if (!sslink) return;
	var json_url = Dase.getJsonUrl();
	if (!json_url) {
		Dase.addClass(sslink,'hide');
		return;
	}
	sslink.onclick = function() {
		Dase.slideshow.start(json_url,Dase.user.eid,Dase.user.htpasswd);
		return false;
	}
}


