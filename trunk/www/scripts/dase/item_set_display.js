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
//taken from item_display -- needs work
Dase.initAddMetadata = function()
{
	var mlink = Dase.$('addMetadataLink');
	var mform = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	if (!mlink || !mform) return;
	mlink.onclick = function() {
		Dase.addClass(Dase.$('adminPageControls'),'hide');
		Dase.removeClass(Dase.$('pageReloader'),'hide');
		Dase.$('pageReloaderLink').onclick = function() {
			Dase.pageReload();
			return false;
		}
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
	return false;
}
}


Dase.initGetInputForm = function(form) {
	coll = Dase.$('collectionAsciiId').innerHTML;
	form.att_ascii_id.onchange = function() { //this is the attribute selector
	var url = Dase.base_href+'attribute/'+coll+'/'+this.options[this.selectedIndex].value+'.json';
	Dase.getJSON(url,function(resp) {
		resp.coll_ser = Dase.$('collSer').innerHTML;
		if (!resp.html_input_type) {
			resp.html_input_type = 'text';
		}
		var templateObj = TrimPath.parseDOMTemplate('input_form_'+resp.html_input_type+'_jst');
		Dase.$('addMetadataFormTarget').innerHTML = templateObj.process(resp);
		var input_form = Dase.$('ajaxFormHolder').getElementsByTagName('form')[1];
		input_form.onsubmit = function() {
			var content_headers = {
				'Content-Type':'application/x-www-form-urlencoded'
			}
			Dase.loadingMsg(true);
			Dase.ajax(this.action,'post',function() { 
				Dase.getJSON(Dase.base_href+'item/'+Dase.$('collSer').innerHTML+'/metadata',function(metadata_json) {
				data = {'meta':metadata_json};
				var templateObj = TrimPath.parseDOMTemplate('metadata_jst');
				Dase.$('metadata').innerHTML = templateObj.process(data);
			});
		},Dase.form.serialize(this),null,null,content_headers); 
		Dase.$('addMetadataFormTarget').innerHTML = '';
		form.att_ascii_id.selectedIndex = 0; //reset attribute selector
		return false;
	};
});
return false;
}
}

