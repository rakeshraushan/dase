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

Dase.$ = function(id) {
	return document.getElementById(id);
}
Dase.user = {};
Dase.base_href = document.getElementsByTagName('base')[0].href;
Dase.setCookieData = function() {
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
		Dase.getJSON(Dase.base_href + "json/" + eid + "/data",function(json){
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
			li.setAttribute('id',c.ascii_id);
			var input = document.createElement('input');
			input.setAttribute('type','checkbox');
			input.setAttribute('name','c');
			input.setAttribute('value',c.ascii_id);
			//input.setAttribute('checked','checked');
			li.appendChild(input);
			li.appendChild(document.createTextNode(' '));
			var a = document.createElement('a');
			a.setAttribute('href',c.ascii_id);
			a.setAttribute('class','checkedCollection');
			a.className = 'checkedCollection';
			a.appendChild(document.createTextNode(c.collection_name));
			li.appendChild(a);
			li.appendChild(document.createTextNode(' '));
			var span = document.createElement('span');
			span.setAttribute('class','tally');
			span.className = 'tally';
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

Dase.initCheckImage = function() { 
	var thumbs = Dase.$('searchResults');
	if (!thumbs) { return; }
	/* creates and initializes list check/uncheck toggle 
	 */
	var boxes = thumbs.getElementsByTagName('input');
	/* changes the color of the image bg when box
	 * next to it is checked/unchecked
	 */
	for (var i=0; i<boxes.length; i++) {
		boxes[i].onclick = function() {
			var img = this.parentNode;
			if (Dase.hasClass(img,'checked')) {
				Dase.removeClass(img,'checked');
			} else {
				Dase.addClass(img,'checked');
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
		Dase.getJSON(Dase.base_href+"json/item_tallies", function(json){
				for(var ascii_id in json) {
				var tally = Dase.$(ascii_id).getElementsByTagName('span')[0];
				if (tally) {
				tally.innerHTML = '(' + json[ascii_id] + ')';
				} } });
	}
}

Dase.placeUserTags = function() {
	var eid = Dase.user.eid;
	var json = Dase.user.tags;
	var tags={};
	var sets = {};
	tags['allTags'] = Dase.$('allTags');
	for (var type in json) {
		var jsonType = json[type];
		for (var ascii in jsonType) {
			if ('cart' != type) {
				var jsonAscii = jsonType[ascii];
				if (sets['allTags']) {
					sets['allTags'] = sets['allTags'] + "<input type='checkbox' name='" + ascii + "'> " + jsonAscii + "</input><br>\n";
				} else {
					sets['allTags'] = "<input type='checkbox' name='" + ascii + "'> " + jsonAscii + "</input><br>\n";
				}
				if (sets[type]) {
					sets[type] = sets[type] + "<li><a href='" + eid + "/tag/" + ascii + "'>" + jsonAscii + "</a></li>\n";
				} else {
					sets[type] = "<li><a href='" + eid + "/tag/" + ascii + "'>" + jsonAscii + "</a></li>\n";
				}
			}
		} 
	}
	for (var type in sets) {
		Dase.$(type).innerHTML = sets[type];

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
			//var tally = Dase.$('tally-'+ascii_id).parentNode.getElementsByTagName('span')[0];
			var tally = Dase.$('tally-'+ascii_id);
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

	// this is to deal with IE6 cache behavior
	var date = new Date();
	url = url + '?' + date.getTime();

	var xmlhttp = Dase.createXMLHttpRequest();
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

Dase.getElementHtml = function(url,target,my_func) {
	//this assumes a DOM node being passed in (NOT elem id)
	if (target) {
		target.innerHTML = '<div class="loading">Loading...</div>';
	}

	// this is to deal with IE6 cache behavior
	//var date = new Date();
	//url = url + '?' + date.getTime();

	var xmlhttp = Dase.createXMLHttpRequest();
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var returnStr = xmlhttp.responseText;
			target.innerHTML = returnStr;
			if (my_func) {
				my_func();
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.getJSON = function(url,my_func) {
	var xmlhttp = Dase.createXMLHttpRequest();

	// this is to deal with IE6 cache behavior
	var date = new Date();
	url = url + '?' + date.getTime();

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

Dase.initSearchResults = function() {
	var results = Dase.$('searchResults');
	var json = Dase.$('itemsJson');
	if (json) {
		var items_json = eval('(' + json.innerHTML + ')');
		for (var name in items_json ) {
			var set = items_json[name];
			var h2 = document.createElement('h2');
			var thumbs = document.createElement('div');
			var spacer = document.createElement('div');
			spacer.className = 'spacer';
			h2.appendChild(document.createTextNode(name + ' (' + set.length + ' items found)'));
			var id_set = set.slice(0,30);
			var id_list = id_set.join();
			results.appendChild(h2);
			results.appendChild(thumbs);
			thumbs.appendChild(document.createTextNode(id_list));
			results.appendChild(spacer);
			//Dase.getElementHtml('html/item/thumbs/' + id_list,thumbs);
		}
	}
}

addLoadEvent(function() {
		Dase.initUser();
		Dase.initMenu('menu');
		Dase.initBrowse();
		Dase.initSearchResults();
		Dase.initCheckImage();
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

