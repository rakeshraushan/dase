Dase.pageInit = function() {
	Dase.initSlideshowLink();
	Dase.initBulkEditLink();
	if (Dase.initSearchSorting) {
		Dase.initSearchSorting();
	}
	Dase.initZoomer();
}

Dase.initBulkEditLink = function() {
	var belink = Dase.$('bulkEditor');
	if (!belink) return;
	var templates_url = Dase.$('jsTemplatesUrl').href;
	if (!templates_url) return;
	var mform = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	Dase.ajax(templates_url,'get',function(resp) {
		Dase.$('jsTemplates').innerHTML = resp;
		belink.onclick = function() {
			if (Dase.toggle(mform)) {
				mform.innerHTML = '<h1 class="loading">Loading...</h1>';
				Dase.getJSON(this.href, function(json){
				var data = { 'atts': json };
				var templateObj = TrimPath.parseDOMTemplate("select_att_jst");
				mform.innerHTML = templateObj.process(data);
				var getForm = Dase.$('getInputForm');
				Dase.initGetInputForm(getForm);
			});
		}
		return  false;
	};
	});
}

Dase.initZoomer = function() {
	var table = Dase.$('itemSet');
	if (!table) return;
	var links = table.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		if ('zoomer' == links[i].className) {
			var z = links[i];
			z.onclick = function() {
				var target = Dase.$('thumb'+this.id.replace(/zoom/,'')); //get img link
				if (target) {
					var thumblink = target.src;
					target.src = this.href;
					this.href = thumblink;
					this.className = 'unzoomer';
					this.innerHTML = '[-]';
					Dase.showElem(target);
					Dase.initZoomer();
				}
				return false;
			}
		}
		if ('unzoomer' == links[i].className) {
			var z = links[i];
			z.onclick = function() {
				var target = Dase.$('thumb'+this.id.replace(/zoom/,'')); //get img link
				if (target) {
					var viewitemlink = target.src;
					target.src = this.href;
					this.href = viewitemlink; 
					this.className = 'zoomer';
					this.innerHTML = '[+]';
					Dase.showElem(target);
					Dase.initZoomer();
				}
				return false;
			}
		}
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

Dase.initGetInputForm = function(form) {
	coll = Dase.$('collectionAsciiId').innerHTML;
	form.att_ascii_id.onchange = function() { //this is the attribute selector
	var url = Dase.base_href+'attribute/'+coll+'/'+this.options[this.selectedIndex].value+'.json';
	Dase.getJSON(url,function(resp) {
		resp.eid_ascii = Dase.$('tagEid').innerHTML+'/'+Dase.$('tagAsciiId').innerHTML;
		if (!resp.html_input_type) {
			resp.html_input_type = 'text';
		}
		var templateObj = TrimPath.parseDOMTemplate('input_form_'+resp.html_input_type+'_jst');
		Dase.$('addMetadataFormTarget').innerHTML = templateObj.process(resp);
	});
	return false;
}
}

