if (!Dase) { var Dase = {}; }

/* from DOM Scripting p. 103 */
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
}

Dase.user = {};

Dase.setCookieData = function() {

}

Dase.$ = function(id) {
	return document.getElementById(id);
}

Dase.getCookieData = function() {
	/*
	   if dase cookie exists, get data
	   else see if there is an eid set on web page
	   if so, ajaxily get data and store in cookie
	   else return null		
	 */
}

Dase.addClass = function(elem,cname) {
	if (elem.className) {
		elem.className = elem.className + " " + cname;
	} else {
		elem.className = cname;
	}
}

Dase.removeClass = function(elem,cname) {
	var cnames = elem.className.split(" ");
	var newClassName = '';
	for (var i=0;i<cnames.length;i++) {
		if (cname != cnames[i]) {
			newClassName = newClassName + " " + cnames[i];
		}
	}
	elem.className = newClassName;
}

Dase.hasClass = function(elem,cname) {
	var cnames = elem.className.split(" ");
	for (var i=0;i<cnames.length;i++) {
		if (cname == cnames[i]) {
			return true;
		}
	}
	return false;
}

Dase.displayError = function(msg) {
	var jsalert = Dase.$('msg');
	Dase.removeClass(jsalert,'hide');
	jsalert.innerHTML = '';
	jsalert.innerHTML = msg;
}

Dase.toggle = function(el) {
	if (Dase.hasClass(el,'hide')) {
		Dase.removeClass(el,'hide');
	} else {
		Dase.addClass(el,'hide');
	}
}

Dase.initUser = function() {
	// from rhino 5th ed. p. 460 
	var allcookies = document.cookie;
	var pos = allcookies.indexOf("DASE_USER=");
	var eid;
	if (pos != -1) {
		var start = pos + 10;
		var end = allcookies.indexOf(";",start);
		if (end == -1) end = allcookies.length;
		eid = allcookies.substring(start,end);
	}
	if (eid) {
		Dase.user.eid = eid;
		Dase.removeClass(Dase.$('logoffControl'),'hide');
		Dase.getJSON("json/" + eid + "/data",function(json){
				Dase.user.name = json[eid]['name'];
				Dase.user.tags = json[eid]['tags'];
				Dase.user.collections = json[eid]['collections'];
				Dase.placeUserName();
				Dase.placeUserTags();
				Dase.placeUserCollections();
				Dase.multicheck("checkedCollection");
				Dase.getItemTallies();
				});
	} else {
		Dase.removeClass(Dase.$('loginControl'),'hide');
	}
}

Dase.placeUserName = function() {
	var nameElem = Dase.$('userName');
	nameElem.innerHTML = Dase.user.name + " " + nameElem.innerHTML;
}

Dase.placeUserCollections = function() {
	var hasSpecial = 0;
	var coll_list = Dase.$('collectionList');
	if (!coll_list) return;
	for (var i=0;i<Dase.user.collections.length;i++) {
		var c = Dase.user.collections[i];
		if ("1" != c.is_public) {
			hasSpecial++;
			var li = document.createElement('li');
			var input = document.createElement('input');
			input.setAttribute('type','checkbox');
			input.setAttribute('name','cols[]');
			input.setAttribute('value',c.id);
			input.setAttribute('checked','checked');
			li.appendChild(input);
			li.appendChild(document.createTextNode(' '));
			var a = document.createElement('a');
			a.setAttribute('href',c.ascii_id);
			a.setAttribute('class','checkedCollection');
			a.appendChild(document.createTextNode(c.collection_name));
			li.appendChild(a);
			li.appendChild(document.createTextNode(' '));
			var span = document.createElement('span');
			span.setAttribute('class','tally');
			span.setAttribute('id','tally-'+c.ascii_id);
			li.appendChild(span);
			coll_list.appendChild(li);
		}
	}
	if (hasSpecial) {
		Dase.removeClass(Dase.$('specialAccessLabel'),'hide');
	}
}

Dase.initMenu = function(id) { 
	var menu = Dase.$(id);
	if (menu) {
		var listItems = menu.getElementsByTagName('li');
		for (var i=0;i<listItems.length;i++) {
			var listItem = listItems[i];
			var sub = listItem.getElementsByTagName('ul');
			if (sub) {
				var listItemLink = listItem.getElementsByTagName('a')[0];
				if (listItemLink) {
					listItemLink.onclick = function() {
						if (!Dase.user.eid) {
							Dase.displayError('You need to be logged in'); 
							return false;
						}
						var child_ul = this.parentNode.getElementsByTagName('ul')[0];
						if (child_ul) {
							Dase.toggle(child_ul);
							return false;
						} else {
							return true;
						}
					}
				}
			}
		}
	}
}

Dase.multicheck = function(c) { 
	var coll_list = Dase.$('collectionList');
	if (!coll_list) { return; }
	/* creates and initializes list check/uncheck toggle 
	 */
	var multi = document.createElement('a');
	multi.setAttribute('href','');
	multi.setAttribute('class','uncheck');
	multi.setAttribute('className','uncheck');
	multi.appendChild(document.createTextNode('check/uncheck all'));
	coll_list.appendChild(multi);
	var boxes = coll_list.getElementsByTagName('input');
	multi.onclick = function() {
		for (var i=0; i<boxes.length; i++) {
			box = boxes[i];
			if ('uncheck' == this.className) {
				//box.removeAttribute('checked');
				box.checked = null;
				box.parentNode.getElementsByTagName('a')[0].className = '';
			} else {
				//box.setAttribute('checked',true);
				box.checked = true;
				box.parentNode.getElementsByTagName('a')[0].className = c;
			}
		}	   
		if ('uncheck' == this.className) {
			this.className = 'check';
		} else {
			this.className = 'uncheck';
		}
		return false;
	}
	/* changes the color of the collection name when box
	 * next to it is checked/unchecked
	 */
	for (var i=0; i<boxes.length; i++) {
		boxes[i].onclick = function() {
			var link = this.parentNode.getElementsByTagName('a')[0];
			if (c == link.className) {
				link.className = '';
			} else {
				link.className = c;
			}
		}
	}	   
}

Dase.getItemTallies = function() {
	if (Dase.$("collectionList")) {
		Dase.getJSON("json/item_tallies", function(json){
				for(var ascii_id in json) {
				var tally = Dase.$('tally-' + ascii_id);
				if (tally) {
				tally.innerHTML = '(' + json[ascii_id] + ')';
				} } });
	}
}

Dase.placeUserTags = function() {
	var eid = Dase.user.eid;
	var json = Dase.user.tags;
	var tags={};
	tags['tagsSelect'] = Dase.$('tagsSelect');
	for (var type in json) {
		var jsonType = json[type];
		for (var ascii in jsonType) {
			if ('cart' != type) {
				var jsonAscii = jsonType[ascii];
				tags['tagsSelect'].innerHTML = tags['tagsSelect'].innerHTML + "<input type='checkbox' name='" + ascii + "'> " + jsonAscii + "</input><br>\n";
				//first time through we grab the element using getElementById
				tags[type] = tags[type] ? tags[type] : Dase.$(type);	
				if (tags[type]) {
					tags[type].innerHTML = tags[type].innerHTML + "<li><a href='" + eid + "/tag/" + ascii + "'>" + jsonAscii + "</a></li>\n";
				} 
			}
		} 
	}
}

Dase.initBrowse = function() {
	if (Dase.$('browseColumns')) {
		var att_coll = Dase.$('attColumn');
		Dase.getAttributes(att_coll.className);
		att_coll.className = '';
		var val_coll = Dase.$('valColumn');
		val_coll.innerHTML = '';

		var cats = Dase.$('catColumn').getElementsByTagName('a');
		for (var i=0;i<cats.length;i++) {
			var cat = cats[i];
			cat.onclick = function() {
				Dase.getAttributes(this.href);
				var cts = Dase.$('catColumn').getElementsByTagName('a');
				for (var j=0;j<cts.length;j++) {
					Dase.removeClass(cts[j],'spill');
				}
				this.className = 'spill';
				return false;
			}
		}
	}
}

Dase.getAttributes = function(url) {
	Dase.getHtml(url,'attColumn',function() {Dase.bindGetValues(Dase.$('collectionAsciiId').className)});
	var val_coll = Dase.$('valColumn');
	val_coll.className = 'hide';
	val_coll.innerHTML = '';
}


Dase.bindGetValues = function(coll) {
	Dase.getAttributeTallies(coll);
	var atts = Dase.$('attColumn').getElementsByTagName('a');
	for (var i=0;i<atts.length;i++) {
		var att_link = atts[i];
		if ('att_link' == att_link.className) {
			att_link.onclick = function() {
				Dase.getHtml(this.href,'valColumn');	
				Dase.removeClass(Dase.$('valColumn'),'hide');
				window.scroll(0,0);
				for (var j=0;j<atts.length;j++) {
					atts[j].className = 'att_link';
				}
				this.className = 'spill';
				return false;
			}
		}
	}
}

Dase.getAttributeTallies = function(coll) {
	var url;
	var is_admin = 0;
	if (Dase.$('get_admin_tallies')) {
		url = "json/" + coll + "/admin_attribute_tallies";
		is_admin = 1;
	}
	if (Dase.$('get_public_tallies')) {
		url = "json/" + coll + "/attribute_tallies";
	}
	if (Dase.$('get_cb_tallies')) {
		url = "json/" + coll + "/cb_attribute_tallies";
	}
	Dase.getJSON(url,function(json) {
			for(var ascii_id in json) {
			var	att_link = Dase.$(ascii_id);
			if (att_link) {
			var tally = Dase.$(ascii_id).parentNode.getElementsByTagName('span')[0];
			if (tally) {
			if (is_admin && 0 == json[ascii_id]) {
			//make admin atts w/ no values disappear
			tally.parentNode.className = 'hide';
			} else {
			tally.innerHTML = '(' + json[ascii_id] + ')';
			}
			if (is_admin) {
			// makes admin atts appear ONLY after tallies are set
			Dase.addClass(Dase.$('get_admin_tallies'),'hide');
			Dase.removeClass(Dase.$('adminAttsLabel'),'hide');
			Dase.removeClass(Dase.$('attList'),'hide');
			}
			} } } });
}

Dase.initDynamicSearchForm = function() {
	jQuery("select.dynamic").change(function() {
			jQuery(this).parent().find("input[type=text]").attr("name",jQuery("option:selected",this).attr("value"));
			});
}


Dase.createXMLHttpRequest = function() {
	var xmlhttp;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		alert('Perhaps your browser does not support xmlhttprequests?');
	}
	return xmlhttp;
}

Dase.getHtml = function(url,elem_id,my_func) {
	var target = Dase.$(elem_id);
	if (target) {
		target.innerHTML = '<div class="loading">Loading...</div>';
	}
	var xmlhttp = Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var returnStr = xmlhttp.responseText;
			Dase.$(elem_id).innerHTML = returnStr;
			if (my_func) {
				my_func();
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.getJSON = function(url,my_func) {
	var xmlhttp = Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var json = eval('(' + xmlhttp.responseText + ')');
			if (my_func) {
				my_func(json);
			} else {
				return json;
			}
		} else {
			// wait for the call to complete
		}
		return false;
	}
}

addLoadEvent(function() {
		Dase.initUser();
		Dase.initMenu('menu');
		Dase.initBrowse();
//		Dase.initDynamicSearchForm();
		/*
		   Dase.prepareAddFileUpload();
		   Dase.prepareAttributeFlags();
		   Dase.prepareCartAdds(); 
		   Dase.prepareDeletable(); 
		   Dase.prepareDeleteCommonValues();
		   Dase.prepareDownload('download');
		   Dase.prepareHelpModule();
		   Dase.prepareHelpPopup();
		   Dase.prepareLinkBack();
		   Dase.prepareMediaLinks();
		   Dase.prepareMine();
		   Dase.prepareTagItems();
		   Dase.prepareUploadValidation();
		   Dase.setAutoReload();
		   Dase.prepareRemoteLaunch('slideshowLaunch');
		   Dase.initResize();
		   Dase.prepareAddMetadata('addMetadata');
		   Dase.prepareAddMetadata('addTagMetadata');
		   Dase.prepareAddMetadata('uploadForm');
		   Dase.prepareEditable();
		   Dase.prepareGenericEditable();
		 */
});

