Dase.pageInit = function() {
	Dase.initSlideshowLink();
	Dase.initBulkEditLink();
	if (Dase.initSearchSorting) {
		Dase.initSearchSorting();
	}
//	Dase.initZoomer();
}

Dase.initBulkEditLink = function() {
	var belink = Dase.$('bulkEditor');
	if (!belink) return;
	var mform = Dase.$('ajaxFormHolder');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	belink.onclick = function() {
		if (Dase.toggle(mform)) {
			mform.innerHTML = '<h1 class="loading">Loading...</h1>';
			Dase.getJSON(this.href, function(atts){
			h = new Dase.htmlbuilder;
			h.add('h1',null,'Add Metadata');
			var form = h.add('form',{'action':'sss','method':'get','id':'getInputForm'});
			var sel = form.add('select',{'name':'att_ascii_id'});
			sel.add('option',{'value':''},'select an attribute');
			for (var i=0;i<atts.length;i++) {
				var att = atts[i];
				sel.add('option',{'value':att.ascii_id},att.attribute_name);
			}
			h.add('div',{'id':'addMetadataFormTarget'});
			h.attach(mform);
			var getForm = Dase.$('getInputForm');
			Dase.initGetInputForm(getForm);
		},null,'sort=attribute_name');
	}
	return  false;
};
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
		Dase.$('addMetadataFormTarget').innerHTML = Dase.getInputForm(resp);
		var select_autofill = Dase.$('select_autofill');
		if  (select_autofill) {
			select_autofill.onchange = function() {
				Dase.$('autofill_target').value = this.options[this.selectedIndex].value;
			}
		}
	});
	return false;
}
}

Dase.getInputForm = function(resp) {
	var vals = resp.values;
	var form = new Dase.htmlbuilder('form',{'method':'post'});
	form.set('action','tag/'+resp.eid_ascii+'/metadata');
	form.add('input',{'type':'hidden','name':'ascii_id','value':resp.ascii_id});
	switch(resp.html_input_type) {
		case 'text':
		form.add('input',{'type':'text','name':'value'});
		break;
		case 'textarea':
		form.add('p').add('textarea',{'name':'value'},' ');
		break;
		case 'radio':
		for (var i=0;i<vals.length;i++) {
			var v = vals[i];
			var p = form.add('p');
			p.add('input',{'type':'radio','name':'value[]','value':v});
			p.add('span',null,' '+v);
		}
		break;
		case 'checkbox':
		for (var i=0;i<vals.length;i++) {
			var v = vals[i];
			var p = form.add('p');
			p.add('input',{'type':'checkbox','name':'value[]','value':v});
			p.add('span',null,' '+v);
		}
		break;
		case 'select':
		var sel = form.add('p').add('select',{'name':'value'});
		sel.add('option',{'value':''},'select one:');
		for (var i=0;i<vals.length;i++) {
			var v = vals[i];
			sel.add('option',{'value':v},v);
		}
		break;
		case 'listbox':
		form.add('p').add('textarea',{'name':'value'},' ');
		break;
		case 'no_edit':
		form.add('input',{'type':'text','name':'value','disabled':'disabled'});
		break;
		case 'text_with_menu':
		form.add('input',{'type':'text','name':'blank','id':'autofill_target'});
		var sel = form.add('p').add('select',{'name':'value','id':'select_autofill'});
		sel.add('option',{'value':''},'select one:');
		for (var i=0;i<vals.length;i++) {
			var v = vals[i];
			sel.add('option',{'value':v},v);
		}
		break;
	}
	form.add('input',{'type':'submit','value':'add'});
	return form.getString();
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

