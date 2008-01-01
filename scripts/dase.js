var Dase;
if (Dase && (typeof Dase != "object" || Dase.NAME)) {
	throw new Error("Namespace 'Dase' already exists");
}

// Create our namespace, and specify some meta-information
Dase = {};
Dase.NAME = "Dase";    // The name of this namespace
Dase.VERSION = 1.0;    // The version of this namespace

/* from DOM Scripting p. 103 */
Dase.addLoadEvent = function(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		};
	}
};

Dase.$ = function(id) {
	return document.getElementById(id);
};

Dase.user = {};
Dase.base_href = document.getElementsByTagName('base')[0].href;

Dase.addClass = function(elem,cname) {
	if (!elem || !cname) return false;
	if (elem.className) {
		elem.className = elem.className + " " + cname;
	} else {
		elem.className = cname;
	}
	return true;
};

Dase.removeClass = function(elem,cname) {
	if (!elem || !cname) return false;
	var cnames = elem.className.split(" ");
	var newClassName = '';
	for (var i=0;i<cnames.length;i++) {
		if (cname != cnames[i]) {
			newClassName = newClassName + " " + cnames[i];
		}
	}
	elem.className = newClassName;
	return true;
};

Dase.hasClass = function(elem,cname) {
	if (!elem || !cname) return false;;
	var cnames = elem.className.split(" ");
	for (var i=0;i<cnames.length;i++) {
		if (cname == cnames[i]) {
			return true;
		}
	}
	return false;
};

Dase.displayError = function(msg) {
	var jsalert = Dase.$('msg');
	Dase.removeClass(jsalert,'hide');
	jsalert.innerHTML = '';
	jsalert.innerHTML = msg;
};

Dase.toggle = function(el) {
	if (Dase.hasClass(el,'hide')) {
		Dase.removeClass(el,'hide');
	} else {
		Dase.addClass(el,'hide');
	}
};

Dase.getEid = function() {
	//adapted from rhino 5th ed. p 460
	var allcookies = document.cookie;
	var pos = allcookies.indexOf("DASE_USER=");
	if (pos != -1) {
		var start = pos + 10;
		var end = allcookies.indexOf(";",start); 
		if (end == -1) end = allcookies.length;
		var value = allcookies.substring(start,end);
		return decodeURIComponent(value);
	} else {
		return false;
	}
};

Dase.initUser = function() {
	var eid = Dase.getEid();
	if (!eid) {
		Dase.removeClass(Dase.$('loginControl'),'hide');
		return;
	}
	Dase.loadingMsg(true);
	Dase.getJSON(Dase.base_href + "json/user/"+eid+ "/data",function(json){
			for (var eid in json) {
			Dase.user.eid = eid;
			Dase.user.name = json[eid].name;
			Dase.user.tags = json[eid].tags;
			Dase.user.collections = json[eid].collections;
			Dase.placeUserName(eid);
			Dase.placeUserTags(eid);
			Dase.placeUserCollections(eid);
			Dase.placeUserSearchCollections();
			Dase.initCart();
			Dase.initAddToCart();
			}
			Dase.loginControl(Dase.user.eid);
			Dase.multicheck("checkedCollection");
			Dase.getItemTallies();
			});
};

Dase.loginControl = function(eid) {
	if (eid) {
		Dase.removeClass(Dase.$('logoffControl'),'hide');
	} else {
		Dase.removeClass(Dase.$('loginControl'),'hide');
	}
}

Dase.placeUserName = function(eid) {
	var nameElem = Dase.$('userName');
	if (nameElem) {
		nameElem.innerHTML = Dase.user.name + " " + nameElem.innerHTML;
		var eidElem = Dase.$('eid');
		eidElem.innerHTML = eid;
	}
};

Dase.placeUserCollections = function(eid) {
	var cartLink = Dase.$('cartLink');
	if (cartLink) {
		cartLink.setAttribute('href','user/'+eid+'/cart/');
	}
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
};

Dase.placeUserSearchCollections = function() {
	var maxCollName = 30;
	var sel = Dase.$('collectionsSelect');
	if (!sel)  return; 
	var opt = document.createElement('option');
	opt.setAttribute('value','');
	opt.appendChild(document.createTextNode('All Public Collections'));
	sel.appendChild(opt);
	if (!sel) return; 
	for (var i=0;i<Dase.user.collections.length;i++) {
		var c = Dase.user.collections[i];
		opt = document.createElement('option');
		opt.setAttribute('value',c.ascii_id);
		var label = Dase.truncate(c.collection_name,maxCollName);
		opt.appendChild(document.createTextNode(label));
		sel.appendChild(opt);
	}
	sel.onchange = function() {
		Dase.setCollectionAtts(this.options[this.selectedIndex].value);
	};

	Dase.$('refineCheckbox').onclick = Dase.searchRefine;
};

Dase.searchRefine = function() {
	var formDiv = Dase.$('refinements');
	if (formDiv.hasChildNodes()) {
		//means this is UNchecking the box
		while (formDiv.childNodes[0]) {
			formDiv.removeChild(formDiv.childNodes[0]);
		}
		//undo any restriction to single collection
		var sel = Dase.$('collectionsSelect');
		while (sel.childNodes[0]) {
			sel.removeChild(sel.childNodes[0]);
		}
		Dase.placeUserSearchCollections();
		Dase.setCollectionAtts('');
		return;
	}
	var single_collection_flag = 0;
	var colls_array = [];
	var re = new RegExp('[^/&=?]*_collection');
	var current = String(Dase.$('self_url').innerHTML);
	var parts = (current.split('?'));
	var url_string = parts[0];
	//note that '+' are replaced in the xslt stylesheet
	var qstring = decodeURI(parts[1]);
	var qpairs = qstring.split('&amp;');
	for (var i=0;i<qpairs.length; i++) {
		var qp = qpairs[i];
		var keyval = qp.split('=');
		if (keyval.length > 1 && keyval[1]) {
			var hidden = document.createElement('input');
			hidden.setAttribute('type','hidden');
			hidden.setAttribute('name',keyval[0]);
			hidden.setAttribute('value',keyval[1]);
			formDiv.appendChild(hidden);
			if ('c' != keyval[0] && 'nc' != keyval[0]) {
				//check for collection_ascii_id in key and value
				var c1 = re.exec(keyval[0]);
				var c2 = re.exec(keyval[1]);
				if (c1) {
					Dase.limitSearchToCollection([c1]);
					single_collection_flag = 1;
				}
				if (c2) {
					Dase.limitSearchToCollection([c2]);
					single_collection_flag = 1;
				}
			} else {
				colls_array.push(keyval[1]);
			}
		}
	}
	var collection = re.exec(url_string);
	if (collection) {
		var hidden = document.createElement('input');
		hidden.setAttribute('type','hidden');
		hidden.setAttribute('name','collection_ascii_id');
		hidden.setAttribute('value',collection);
		formDiv.appendChild(hidden);
		Dase.limitSearchToCollection([collection]);
		single_collection_flag = 1;
	} 
	if (!single_collection_flag && colls_array.length) {
		Dase.limitSearchToCollection(colls_array);
	}
};

Dase.limitSearchToCollection = function(c_ascii_array) {
	//remove all collection option except for c_ascii_array
	var keepers = [];
	var sel = Dase.$('collectionsSelect');
	while (sel.childNodes[0]) {
		for (var i=0;i<c_ascii_array.length;i++) {
			if (sel.childNodes[0].value == c_ascii_array[i]) {
				keepers.push(sel.childNodes[0]);
			}
		}
		sel.removeChild(sel.childNodes[0]);
	}
	for (var j=0;j<keepers.length;j++) { 
		sel.appendChild(keepers[j]);
	}
	if (1 == c_ascii_array.length) {
		Dase.setCollectionAtts(c_ascii_array[0]);
	} else {
		var opt = document.createElement('option');
		opt.setAttribute('value','');
		opt.setAttribute('selected','selected');
		opt.appendChild(document.createTextNode('Current Collections'));
		sel.appendChild(opt);
		Dase.setCollectionAtts('');
	}
};

Dase.setCollectionAtts = function(coll) {
	//you can pass in a coll OR use as an event handler
	var sel = Dase.$('attributesSelect');
	if (!sel) return; 
	sel.onchange = Dase.specifyQueryType;
	var maxAttName = 40;
	if ('' === coll) {
		/* per http://raibledesigns.com/rd/entry/javascript_removechild_howto */
		while (sel.childNodes[0]) {
			sel.removeChild(sel.childNodes[0]);
		}
		Dase.addClass(Dase.$('preposition'),'hide');
		Dase.addClass(sel,'hide');
	} else {
		Dase.getJSON(Dase.base_href + "json/" + coll + "/attributes",function(json){
				//fixes problem in which search string defined a collection_ascii
				//AND refinement to THIS collection did also and for some reason the att
				//list got populated twice
				//(it's because the child removal above gets called on the second call
				//of this function BEFORE this async call is complete, so you have
				//remove-remove-add-add rather than remove-add-remove-add
				while (sel.childNodes[0]) {
				sel.removeChild(sel.childNodes[0]);
				}
				var opt = document.createElement('option');
				opt.setAttribute('value',"");
				opt.appendChild(document.createTextNode("All Attributes"));
				sel.appendChild(opt);
				for (var i=0;i<json.length;i++) {
				var att = json[i];
				var opt = document.createElement('option');
				opt.setAttribute('value',att.collection+'%'+att.ascii_id);
				var label = Dase.truncate(att.attribute_name,maxAttName);
				opt.appendChild(document.createTextNode(label));
				sel.appendChild(opt);
				}
				Dase.removeClass(Dase.$('preposition'),'hide');
				Dase.removeClass(sel,'hide');
				});
	}
};


//created for Persion Online & not used in DASe just yet
Dase.initRowTable = function(id,new_class) {
	var table = Dase.$(id);
	if (!table) return;
	var rows = table.getElementsByTagName('tr');
	for (var i=0;i<rows.length;i++) {
		var row = rows[i];
		row.onmouseover = function() {
			var cells = this.getElementsByTagName('td');
			for (var i=0;i<cells.length;i++) {
				cells[i].className = new_class;
			}
		};
		row.onmouseout = function() {
			var cells = this.getElementsByTagName('td');
			for (var i=0;i<cells.length;i++) {
				cells[i].className = "";
			}
		};
	}
};

Dase.specifyQueryType = function() {
	var opt = this.options[this.selectedIndex];
	var query = Dase.$('queryInput');
	if (!query) return; 
	query.name = opt.value;
};

Dase.truncate = function(str,len) {
	if (str.length <= len ) return str;
	var small = str.slice(0,len);
	small = small + '...';
	return small.toString();
};

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
					};
				}
			}
		}
	}
};

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
			var img = this.parentNode.parentNode;
			if (Dase.hasClass(img,'checked')) {
				Dase.removeClass(img,'checked');
			} else {
				Dase.addClass(img,'checked');
			}
		};
	}	   
};

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
			var box = boxes[i];
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
	};
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
		};
	}	   
};

Dase.getItemTallies = function() {
	if (Dase.$("collectionList")) {
		Dase.getJSON(Dase.base_href+"json/item_tallies", function(json){
				for(var ascii_id in json) {
				var asc = Dase.$(ascii_id);
				if (asc) {
				var tally = Dase.$(ascii_id).getElementsByTagName('span')[0];
				if (tally) {
				tally.innerHTML = '(' + json[ascii_id] + ')';
				} } } 
				Dase.loadingMsg(false);
				});
	}
};

Dase.loadingMsg = function(displayBool) {
	var loading = Dase.$('ajaxMsg');
	if (!loading) return;
	if (displayBool) {
		loading.innerHTML = 'loading page data...';
		setTimeout('Dase.loadingMsg(false)',3000);
	} else {
		var loading = Dase.$('ajaxMsg');
		loading.innerHTML = '';
	}
}

Dase.placeUserTags = function(eid) {
	var json = Dase.user.tags;
	var tags={};
	var sets = {};
	tags.allTags = Dase.$('allTags');
	for (var type in json) {
		var jsonType = json[type];
		for (var ascii in jsonType) {
			if ('cart' != type) {
				var jsonAscii = jsonType[ascii];
				//we populate 'sets' as we go
				//populate list of ALL tags that can be copied to from cart
				//concat...
				if (sets.allTags) {
					sets.allTags = sets.allTags + "<input type='checkbox' name='" + ascii + "'> " + jsonAscii + "</input><br>\n";
				} else {
					//first time through...
					sets.allTags = "<input type='checkbox' name='" + ascii + "'> " + jsonAscii + "</input><br>\n";
				}
				if (sets[type]) {
					//concat...
					//note that subscriptions don't REALLY use the ascii_id -- it's actually the tag id
					if ('subscription' == type) {
						var id = ascii.substring(1);
						sets[type] = sets[type] + "<li><a href='tag/" + id  + "'>" + jsonAscii + "</a></li>\n";
					} else {
						sets[type] = sets[type] + "<li><a href='user/" + eid + "/tag/" + ascii + "'>" + jsonAscii + "</a></li>\n";
					}
				} else {
					//first time through...
					if ('subscription' == type) {
						id = ascii.substring(1);
						sets[type] = "<li><a href='tag/" + id + "'>" + jsonAscii + "</a></li>\n";
					} else {
						sets[type] = "<li><a href='user/" + eid + "/tag/" + ascii + "'>" + jsonAscii + "</a></li>\n";
					}
				}
			} else { // cart tally
				var cart_tally = Dase.$('cart_tally');
				if (cart_tally) {
					cart_tally.innerHTML = jsonType[ascii];
				}
			}
		} 
	}
	for (var type in sets) {
		try{
			Dase.$(type).innerHTML = sets[type];
		} catch(e) {
			alert('a friendly notice: ' +e);
		}
	}
};

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
			};
		}
	}
};

Dase.getAttributes = function(url) {
	Dase.getHtml(url,'attColumn',function() {
			Dase.bindGetValues(Dase.$('collectionAsciiId').className);
			});
	var val_coll = Dase.$('valColumn');
	val_coll.className = 'hide';
	val_coll.innerHTML = '';
};


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
			};
		}
	}
};

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
			} } }
			Dase.loadingMsg(false);
		   	});
};

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
};

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
	};
};

Dase.ajax = function(url,method,my_func) {
	if (!method) {
		method = 'POST';
	}
	var xmlhttp = Dase.createXMLHttpRequest();
	xmlhttp.open(method, url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var returnStr = xmlhttp.responseText;
			if (my_func) {
				my_func(returnStr);
			}
		} else {
			// wait for the call to complete
		}
	};
};

Dase.getElementHtml = function(url,target,my_func) {
	//this assumes a DOM node being passed in (NOT elem id)
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
			target.innerHTML = returnStr;
			if (my_func) {
				my_func();
			}
		} else {
			// wait for the call to complete
		}
	};
};

Dase.getJSON = function(url,my_func) {
	var xmlhttp = Dase.createXMLHttpRequest();

	// this is to deal with IE6 cache behavior
	// also note that JSON data needs to be up-to-the-second
	// accurate given the way we currently do deletes!
	var date = new Date();
	url = url + '?' + date.getTime();

	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200 && xmlhttp.responseText) {
				var jsonObj = eval('(' + xmlhttp.responseText + ')');
				var json = jsonObj.json;
				if (my_func) {
					my_func(json);
				} else {
					return json;
				}
			} else {
				var json = {};
				if (my_func) {
					my_func(json);
				} else {
					return json;
				}
			}
		} else { 	
			// wait for the call to complete
		}
		return false;
	};
};

Dase.initAddToCart = function() {
	var sr = Dase.$('searchResults');
	if (!sr) return;
	var anchors = sr.getElementsByTagName('a');
	for (var i=0;i<anchors.length;i++) {
		if ('add to cart' == anchors[i].innerHTML) {
			anchors[i].onclick = function(e) {
				this.innerHTML = '(remove)';
				Dase.removeClass(this.parentNode.getElementsByTagName('span')[0],'hide');
				var inputElem = this.parentNode.parentNode.getElementsByTagName('input')[0];
				var item = {};
				item.item_id = inputElem.value;
				HTTP.post(Dase.base_href + 'user/' + Dase.user.eid + "/cart",item,
						function(resp) { 
						Dase.initCart(); 
						});
				return false;
			};
			Dase.removeClass(anchors[i],'hide');
		}
	}
};

Dase.initCart = function() {
	Dase.loadingMsg(true);
	var sr = Dase.$('searchResults');
	if (!sr) return;
	Dase.getJSON(Dase.base_href + 'json/user/' + Dase.user.eid + "/cart",
			function(json) { 
			var cart_tally = Dase.$('cart_tally');
			if (cart_tally) {
			if (undefined !== json.length) {
			cart_tally.innerHTML = json.length;
			} else {
			cart_tally.innerHTML = 0;
			}
			}
			for (var i=0;i<json.length;i++) {
			var in_cart = Dase.$('addToCart_'+ json[i].item_id);
			if  (in_cart) {
			//by default all search result thumbnails have an 'add to cart' link
			//with id = addToCart_{item_id} when this initCart function runs,
			//items currently in cart have link changed to '(remove)', the
			//'in cart' label is unhidden, and the link id is set to removeFromCart_{tag_item_id}
			//and the href is created that, sent with 'delete' http method, will
			//delete item from user's cart
			in_cart.innerHTML = '(remove)';
			in_cart.id = 'removeFromCart_'+json[i].tag_item_id;
			in_cart.href=Dase.base_href + 'user/' + Dase.user.eid + '/tag_items/' + json[i].tag_item_id;
			Dase.removeClass(in_cart.parentNode.getElementsByTagName('span')[0],'hide');
			Dase.addClass(in_cart,'inCart');
			in_cart.item_id = json[i].item_id;
			in_cart.onclick = function() {
				//first, optimistically assume delete will work
				//and reset this link to be an 'add to cart' link
				this.innerHTML = 'add to cart';
				this.id = 'addToCart_' + this.item_id;
				var delete_url = this.href;
				this.href = '#';
				Dase.addClass(this.parentNode.getElementsByTagName('span')[0],'hide');
				Dase.ajax(delete_url,'DELETE',function(resp) {
						Dase.initCart();
						Dase.initAddToCart();
						});
				return false;
			};
			}
			}
			});
};

Dase.addLoadEvent(function() {
		Dase.initUser();
		Dase.initMenu('menu');
		Dase.initBrowse();
		Dase.initCheckImage();
		//Dase.initRowTable('writing','highlight');
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

