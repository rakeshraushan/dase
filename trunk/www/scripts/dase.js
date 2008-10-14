var Dase;
if (Dase && (typeof Dase != "object" || Dase.NAME)) {
	throw new Error("Namespace 'Dase' already exists");
}

// Create our namespace, and specify some meta-information
Dase = {};
Dase.NAME = "Dase";    // The name of this namespace
Dase.user = {};
Dase.util = {};
Dase.widget = {};
//note: since modules create a module-specific base href, we need to strip module/<mod_name>
Dase.base_href = document.getElementsByTagName('base')[0].href.replace(/\/modules\/[^/]*/,'');

/* utilities */

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

Dase.util.trim = function(str) {
	//from: http://blog.stevenlevithan.com/archives/faster-trim-javascript
	var	str = str.replace(/^\s\s*/, ''),
	ws = /\s/,
	i = str.length;
	while (ws.test(str.charAt(--i)));
	return str.slice(0, i + 1);
}

Dase.util.map = function(arr, f, f_a) {
    var r = [];
    arr.length || arr instanceof Array || arr instanceof HTMLCollection || (arr = [arr])
    for(a in arr) arr[a] instanceof Function || r.push(f(arr[a]));
    typeof f_a != 'function' || (r = f_a(r));
    return r;
}

Dase.util.reduce = function(arr, prep, op) {
    arr instanceof Array || (arr = []);
    var r = null;
    typeof prep == 'function' || (prep = function(x) { return x; });
    typeof op == 'function' || (op = function(prev, x) { return prev + x; });
    for(a in arr)  r = op(r, prep(arr[a]));
    return r;
}

Dase.util.select = function(arr, f) {
    typeof f == 'function' || (f = function(x) { return x; });
    var r = [];
    for(a in arr) (rn = f(arr[a])) && r.push(arr[a]);
    return r;
}

Dase.util.filter = function() {
    return select.apply(this, arguments);
}

Dase.util.ofilter = function(obj, f) {
    typeof f == 'function' || (f = function(x,y) { return y; });
    var r = [];
    for(k in obj) (rn = f(k, obj[k])) && (r[k] = obj[k]);
    return r;
}

Dase.util.keys = function(o) {
    var ks = [];
    for(k in o) { typeof o[k] == 'function' || (ks.push(k)); }
    return ks;
}

Dase.util.values = function(o) {
    var vs = [];
    for(k in o) { typeof o[k] == 'function' || (vs.push(o[k])); }
    return vs;
}

Dase.logoff = function() {
	if (Dase.user.eid) {
		Dase.ajax(Dase.base_href + 'login/' + Dase.user.eid,'DELETE',
		function(resp) { 
			window.location.href = Dase.base_href+'login/form';
		});
	} else {
		window.location.href = Dase.base_href+'login/form';
	}
}

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

Dase.getElementsByClass = function(parent,cname,tagname) {
	var res = [];
	var c;
	if (tagname) {
		c = parent.getElementsByTagName(tagname);
	} else {
		c = parent.getElementsByTagName('*');
	}
	for (var i=0;i<c.length;i++) {
		if (Dase.hasClass(c[i],cname)) {
			res.push(c[i]);
		}
	}
	return res;
}

//use to hide 'em
Dase.addClassToChildren = function(elem,cname) {
	if (!elem) return;
	var children = elem.getElementsByTagName('*');
	for (var i=0;i<children.length;i++) {
		Dase.addClass(children[i],cname);
	}
}

Dase.displayError = function(msg) {
	var jsalert = Dase.$('msg');
	if (!jsalert) return false;
	Dase.removeClass(jsalert,'hide');
	jsalert.innerHTML = '';
	jsalert.innerHTML = msg;
	return true;
};

Dase.toggle = function(el) {
	if (Dase.hasClass(el,'hide')) {
		Dase.removeClass(el,'hide');
		return true;
	} else {
		Dase.addClass(el,'hide');
		return false;
	}
};

Dase.createHtmlSet = function(parent,set,tagName) {
	for (var i=0;i<set.length;i++) {
		Dase.createElem(parent,set[i],tagName);
	}
};

Dase.createElem = function(parent,value,tagName,className,id) {
	if (!parent) {
		//alert('no parent');
		return;
	}
	var element = document.createElement(tagName);
	element.style.visibility = 'hidden';
	if (value) {
		element.appendChild(document.createTextNode(value));
	}
	parent.appendChild(element);
	if (className) {
		element.className = className;
	}
	if (id) {
		element.id = id;
	}
	element.style.visibility = 'visible';
	return element;
};

Dase.removeChildren = function(target) {
	if (!target) return;
	while (target.childNodes[0]) {
	target.removeChild(target.childNodes[0]);
}
}

Dase.highlight = function(target,time,cname) {
	if (!cname) {
		cname = 'highlight';
	}
	Dase.addClass(target,cname);
	setTimeout(function() {
		Dase.removeClass(target,cname);
	},time);
}

Dase.removeFromArray = function(ar,val) {
	for (var i=0;i<ar.length;i++) {
		if (val == ar[i]) {
			ar.splice(i,1);
		}
	}
}

/* end utilities */

Dase.getEid = function() {
	var base = Dase.base_href;
	var d = new Date();
	var cookiename = base.substr(7,base.length-8).replace(/\/|\./g,'_') + '_' + 'DASE_USER';
	//adapted from rhino 5th ed. p 460
	var allcookies = document.cookie;
	var pos = allcookies.indexOf(cookiename + "=");
	if (pos != -1) {
		var start = pos + cookiename.length + 1;
		var end = allcookies.indexOf(";",start); 
		if (end == -1) end = allcookies.length;
		var value = allcookies.substring(start,end);
		return decodeURIComponent(value);
	} else {
		return false;
	}
};

Dase.initUser = function(func) {
	var eid = Dase.getEid();
	if (!eid) {
		Dase.removeClass(Dase.$('loginControl'),'hide');
		return;
	}
	Dase.loadingMsg(true);
	var url = Dase.base_href + "user/"+eid+ "/data"
	Dase.getJSON(url,function(json){
		for (var eid in json) {
			Dase.user.eid = eid;
			Dase.user.htpasswd = json[eid].htpasswd;
			Dase.user.name = json[eid].name;
			Dase.user.tags = json[eid].tags;
			Dase.user.collections = json[eid].collections;
			Dase.user.is_superuser = json[eid].is_superuser;
			Dase.user.cart_count = json[eid].cart_count;
			Dase.placeUserName(eid);
			Dase.placeUserTags(Dase.user);
			Dase.placeUserCollections(eid);
			Dase.placeCollectionAdminLink(eid);
			Dase.placeManageLink(eid);
			Dase.initItemEditing(eid);
			Dase.initCart();
			Dase.initAddToCart();
			if (func) {
				func();
			}
		}
		Dase.loginControl(Dase.user.eid);
		Dase.multicheck("checkedCollection");
	});
};

Dase.loginControl = function(eid) {
	if (eid) {
		Dase.removeClass(Dase.$('logoffControl'),'hide');
	} else {
		Dase.removeClass(Dase.$('loginControl'),'hide');
	}
};

Dase.initLogoff = function() {
	var link = Dase.$('logoff-link');
	if (!link) return;
	link.onclick = function() {
		Dase.logoff();
		return false;
	};
};

Dase.placeUserName = function(eid) {
	var nameElem = Dase.$('userName');
	if (nameElem) {
		/*	nameElem.innerHTML = Dase.user.name;
		 */
		nameElem.innerHTML = eid;
		var settingsElem = Dase.$('settings-link');
		settingsElem.href = 'user/'+eid+'/settings';
		var eidElem = Dase.$('eid');
		eidElem.innerHTML = eid;
	}
};

Dase.checkAdminStatus = function(eid) {
	var current_coll_elem = Dase.$('collectionAsciiId');  
	if (!current_coll_elem) return;
	var current_coll = current_coll_elem.innerHTML;  
	for (var i=0;i<Dase.user.collections.length;i++) {
		var c = Dase.user.collections[i];
		//display link to administer collection if user has privs
		if (current_coll && (c.ascii_id == current_coll)) {  
			var auth_info = {
				'collection_ascii_id':current_coll,
				'eid':eid,
				'auth_level':c.auth_level,
				'collection_name':c.collection_name
			}
			return auth_info;
		}
	}
	return false;
};

Dase.initItemEditing = function(eid) {
	var auth_info = Dase.checkAdminStatus(eid);
	if (!auth_info) return;
	var edit_link = Dase.$('editLink');
	if (!edit_link) return;
	var controls = Dase.$('adminPageControls');
	if (auth_info.auth_level == 'manager' || auth_info.auth_level == 'superuser' || auth_info.auth_level == 'write')
	{
		Dase.removeClass(controls,'hide');
		//get jstemplates by making an ajax request
		//see templates/item/jstemplates.tpl
		Dase.ajax(Dase.$('jsTemplatesUrl').href,'get',function(resp) {
			Dase.$('jsTemplates').innerHTML = resp;
			Dase.updateItemStatus();
			Dase.initEditLink(edit_link);
			Dase.initAddMetadata();
		});
	}
	return;
};

Dase.updateItemStatus = function() {
	var status_controls = Dase.$('adminStatusControls');
	Dase.getJSON(Dase.base_href+status_controls.className+'.json',function(json){
			var data = {'status':json};
			var templateObj = TrimPath.parseDOMTemplate("item_status_jst");
			status_controls.innerHTML = templateObj.process(data);
			var form = Dase.$('updateStatus');
			form.onsubmit = function() {
			Dase.ajax(Dase.base_href+status_controls.className,'put',function(resp) {
				Dase.updateItemStatus();
				},form.status.value);
			return false;
			}
			});
}

Dase.placeManageLink = function(eid) {
	var manageLink = Dase.$('manageLink');
	if (manageLink && Dase.user.is_superuser) {
		manageLink.setAttribute('href','admin');
		manageLink.innerHTML = 'DASe Archive Admin';
		Dase.removeClass(manageLink,'hide');
	}
};

Dase.placeCollectionAdminLink = function(eid) {
	var auth_info = Dase.checkAdminStatus(eid);
	if (!auth_info) return;
	var adminLink = Dase.$('adminLink');
	if (auth_info.auth_level == 'manager' || auth_info.auth_level == 'superuser') {
		adminLink.setAttribute('href','manage/'+auth_info.collection_ascii_id);
		adminLink.innerHTML = 'Manage '+auth_info.collection_name;
		Dase.removeClass(adminLink,'hide');
	}
};

Dase.placeUserCollections = function(eid) {
	var cartLink = Dase.$('cartLink');
	if (cartLink) {
		cartLink.setAttribute('href','user/'+eid+'/cart');
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
			a.setAttribute('href','collection/'+c.ascii_id);
			a.setAttribute('class','checkedCollection');
			a.className = 'checkedCollection';
			a.appendChild(document.createTextNode(c.collection_name));
			li.appendChild(a);
			li.appendChild(document.createTextNode(' '));
			var span = document.createElement('span');
			span.setAttribute('class','tally');
			span.className = 'tally';
			span.appendChild(document.createTextNode('('+c.item_count+')'));
			li.appendChild(span);
			coll_list.appendChild(li);
		}
	}
	if (hasSpecial) {
		//this simply shows the "Special Access Collections" subhead
		Dase.removeClass(Dase.$('specialAccessLabel'),'hide');
	}
};

Dase.setCollectionAtts = function() {
	//you can pass in a coll OR use as an event handler
	var coll_el = Dase.$('collectionAsciiId');
	if (coll_el) {
		var	coll = coll_el.innerHTML;
	}
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
	Dase.getJSON(Dase.base_href + "collection/" + coll + "/attributes",function(json){
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
		opt.setAttribute('value',att.collection+'~'+att.ascii_id);
		var label = Dase.truncate(att.attribute_name,maxAttName);
		opt.appendChild(document.createTextNode(label));
		sel.appendChild(opt);
	}
	Dase.removeClass(Dase.$('preposition'),'hide');
	Dase.removeClass(sel,'hide');
});
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
							//Dase.displayError('You need to be logged in'); 
							Dase.logoff();
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

Dase.multicheckItems = function(className) {
	if (!className) {
		className = 'check';
	}
	var item_set = Dase.$('itemSet');
	if (!item_set)  return; 
	target = Dase.$('checkall');
	if (!target)  return; 
	target.className = className;
	var boxes = item_set.getElementsByTagName('input');
	if (!boxes.length) {
		target.className = 'hide';
		var tag_name_el = Dase.$('tag_name');
		if (tag_name_el) {
			//todo: this should REALLY be implemented as a 
			//'delete' request (using XHR to hijack form submit)
			var button = Dase.$('removeFromSet');
			button.name = 'delete_tag';
			button.value = 'Delete '+tag_name_el.innerHTML;
			button.onclick = null;
		}
		return;
	}
	target.onclick = function() {
		if ('uncheck' == this.className) {
			for (var i=0; i<boxes.length; i++) {
				boxes[i].checked = false;
			}	   
			this.className = 'check';
		} else {
			for (var i=0; i<boxes.length; i++) {
				boxes[i].checked = true;
			}	   
			this.className = 'uncheck';
		}
		Dase.multicheckItems(this.className);
		return false;
	};
};

Dase.multicheck = function(c) { 
	var coll_list = Dase.$('collectionList');
	if (!coll_list) { return; }
	target = Dase.$('checkall');
	if (!target) { return; }
	//class of the link determines its behaviour
	target.className = 'uncheck';
	var boxes = coll_list.getElementsByTagName('input');
	target.onclick = function() {
		for (var i=0; i<boxes.length; i++) {
			var box = boxes[i];
			if ('uncheck' == this.className) {
				box.checked = null;
				box.parentNode.getElementsByTagName('a')[0].className = '';
			} else {
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

Dase.loadingMsg = function(displayBool) {
	var loading = Dase.$('ajaxMsg');
	if (!loading) return;
	if (displayBool) {
		Dase.removeClass(loading,'hide');
		loading.innerHTML = 'loading page data...';
		setTimeout('Dase.loadingMsg(false)',1500);
	} else {
		Dase.addClass(loading,'hide');
		loading.innerHTML = '';
	}
}

Dase.placeUserTags = function(user) {
	if (!Dase.$('sets_jst')) return;
	var templateObj = TrimPath.parseDOMTemplate("sets_jst");
	Dase.$('sets-submenu').innerHTML = templateObj.process(user);

	var templateObj = TrimPath.parseDOMTemplate("subscriptions_jst");
	Dase.$('subscription-submenu').innerHTML = templateObj.process(user);

	var templateObj = TrimPath.parseDOMTemplate("saveto_jst");
	var target = Dase.$('saveChecked');
	var item_set = Dase.$('itemSet');
	if (item_set) {
		var items = item_set.getElementsByTagName('td');
	}
	if (target && item_set && items.length) {
		target.innerHTML = templateObj.process(user);
	}

	Dase.initCreateNewSet();
}

Dase.initCreateNewSet = function() {
	var createNewSetLink = Dase.$('createNewSet');
	if (createNewSetLink) {
		createNewSetLink.onclick = function() {
			var tag = {};
			tag.tag_name = prompt("Enter name of set","");
			HTTP.post(Dase.base_href + 'tags',tag,
			function(resp) { 
				Dase.initUser(); 
				Dase.initSaveTo();
				alert(resp);
			},
			function(code,resp) {
				alert(resp);
			});
			return false;
		};
	}
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
			if (Dase.$(elem_id)) {
				Dase.$(elem_id).innerHTML = returnStr;
			}
			if (my_func) { 
				my_func(); 
			}
		}
	};
};

Dase.ajax = function(url,method,my_func,msgBody,username,password,content_headers) {
	if (!method) {
		method = 'POST';
	}
	var xmlhttp = Dase.createXMLHttpRequest();
	if (username && password) {
		xmlhttp.open(method,url,true,username,password);
	} else {
		xmlhttp.open(method,url,true);
	}
	if (content_headers) {
		for (var k in content_headers) {
			xmlhttp.setRequestHeader(k,content_headers[k]);
		}
	}
	if (msgBody) {
		xmlhttp.send(msgBody);
	} else {
		xmlhttp.send(null);
	}
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var returnStr = xmlhttp.responseText;
			if (my_func) {
				my_func(returnStr);
			}
		} 
		if (xmlhttp.readyState == 4 && xmlhttp.status != 200) {
			if (my_func) {
				//todo: think about this
				my_func(xmlhttp.getResponseHeader('Location'));
			}
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
		} 
	};
};

Dase.getJSON = function(url,my_func,error_func,params,username,password) {
	var xmlhttp = Dase.createXMLHttpRequest();
	// this is to deal with IE6 cache behavior
	// also note that JSON data needs to be up-to-the-second
	// accurate given the way we currently do deletes!
	var date = new Date();

	//per http://www.subbu.org/weblogs/main/2005/10/xmlhttprequest.html
	//this may be unnecessary
	if (params) {
		//url = url + '?' + params +'&format=json';
		url = url + '?' + params + '&cache_buster=' + date.getTime()+'&format=json';
	} else {
		//url = url + '?format=json';
		url = url + '?cache_buster=' + date.getTime()+'&format=json';
	}

	if (username && password) {
		xmlhttp.open('GET', url, true,username,password);
	} else {
		xmlhttp.open('GET', url, true);
	}
	//xmlhttp.setRequestHeader('If-Modified-Since', 'Wed, 15 Nov 1970 00:00:00 GMT');
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200 && xmlhttp.responseText) {
				//alert(xmlhttp.responseText);
				var jsonObj = JSON.parse(xmlhttp.responseText);
				//todo: decide on whether to wrap json in 'json' or not
				if (jsonObj.json){ 
					var json = jsonObj.json;
				} else {
					var json = jsonObj;
				}
				if (my_func) {
					my_func(json);
				} else {
					return json;
				}
				} else { //non 200 status returned
				var json = {};
				if (my_func) {
					my_func(json);
				} else {
					return json;
				}
			}
		} 
		return false;
	};
};

Dase.initAddToCart = function() {
	var tag_type_data = Dase.$('tagType');
	if (tag_type_data) {
		var tag_type = tag_type_data.innerHTML;
		//do not display 'add to cart' for user colls & slideshows
		if ('slideshow' == tag_type || 'set' == tag_type) {
			Dase.initCart(); 
			return;
		}
	}
	var sr = Dase.$('itemSet');
	if (!sr) return;
	var anchors = sr.getElementsByTagName('a');
	for (var i=0;i<anchors.length;i++) {
		if ('add to cart' == anchors[i].innerHTML) {
			anchors[i].onclick = function(e) {
				this.innerHTML = '(remove)';
				Dase.removeClass(this.parentNode.getElementsByTagName('span')[0],'hide');
				var inputElem = this.parentNode.parentNode.getElementsByTagName('input')[0];
				var item = {};
				item.item_unique = inputElem.value;
				HTTP.post(Dase.base_href + 'user/' + Dase.user.eid + "/cart",item,
				function(resp) { 
				alert(resp);
					Dase.initUser(); 
					Dase.initSaveTo();
				});
				return false;
			};
			Dase.removeClass(anchors[i],'hide');
		}
	}
};

Dase.initCart = function() {
	Dase.loadingMsg(true);
	var label = Dase.$('cartLink');
	if (label) {
		label.innerHTML = "My Cart ("+Dase.user.cart_count+")";
	}
	var sr = Dase.$('itemSet');
	if (!sr) return;
	Dase.getJSON(Dase.base_href + 'user/' + Dase.user.eid + "/cart",
	function(json) { 
		for (var i=0;i<json.length;i++) {
			var in_cart = Dase.$('addToCart_'+ json[i].item_unique);
			if  (in_cart) {
				//by default all search result thumbnails have an 'add to cart' link
				//with id = addToCart_{item_unique} when this initCart function runs,
				//items currently in cart have link changed to '(remove)', the
				//'in cart' label is unhidden, and the link id is set to removeFromCart_{tag_item_id}
				//and the href is created that, sent with 'delete' http method, will
				//delete item from user's cart
				in_cart.innerHTML = '(remove)';
				in_cart.id = 'removeFromCart_'+json[i].tag_item_id;
				in_cart.href=Dase.base_href + 'user/' + Dase.user.eid + '/tag_items/' + json[i].tag_item_id;
				Dase.removeClass(in_cart.parentNode.getElementsByTagName('span')[0],'hide');
				Dase.addClass(in_cart,'inCart');
				in_cart.item_unique = json[i].item_unique;
				in_cart.onclick = function() {
					//first, optimistically assume delete will work
					//and reset this link to be an 'add to cart' link
					this.innerHTML = 'add to cart';
					this.id = 'addToCart_' + this.item_unique;
					var delete_url = this.href;
					this.href = '#';
					Dase.addClass(this.parentNode.getElementsByTagName('span')[0],'hide');
					Dase.ajax(delete_url,'DELETE',function(resp) {
						Dase.initUser(); 
						Dase.initSaveTo();
					});
					return false;
				};
			}
		}
	});
};

/* Looks for any link w/ class 'toggle'.  That link should have
 * an id that begins 'toggle_' and the remaining string is the
 * id of the element-to-be-toggled.
 * 
 */

Dase.initToggle = function() {
	var links = document.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		if (Dase.hasClass(links[i],'toggle')) {
			var toggle = links[i];
			toggle.onclick = function() {
				var target = this.id.substr(7);
				Dase.toggle(Dase.$(target));
				return false;
			}
		}
	}
};

Dase.initSaveTo = function() {
	var form = Dase.$('saveToForm');
	if (!form) return;
	var itemSet = Dase.$('itemSet');
	if (!itemSet) return;
	form.onsubmit = function() {
		var saveToSelect = Dase.$('saveToSelect');
		var tag_ascii_id = saveToSelect.options[saveToSelect.options.selectedIndex].value;
		var item_uniques_array = [];
		var inputs = itemSet.getElementsByTagName('input');
		for (var i=0;i<inputs.length;i++) {
			if ('item_unique[]' == inputs[i].name && true == inputs[i].checked) {
				//item_uniques_array[item_uniques_array.length] = encodeURIComponent(inputs[i].value);
				item_uniques_array[item_uniques_array.length] = inputs[i].value;
				inputs[i].checked = false;
			}
		}
		if (!item_uniques_array.length) {
			alert('Please check at least one item.');
			return false;
		}
		if (!tag_ascii_id) {
			alert('Please select a user collection/slideshow/cart to save items to.');
			return false;
		}
		var data = {};
		data.item_uniques = item_uniques_array;
		HTTP.post(Dase.base_href + 'tag/' + Dase.user.eid + "/"+tag_ascii_id,data,
		function(resp) { 
			alert(resp); 
			Dase.initUser();
			Dase.initSaveTo();
		},
		//should *always* handle errors w/ an error callback:
		function(resp) {
			alert('Our sincerest apologies.  And error has occurred');
		});
		return false;
	};
};

Dase.initRemoveItems = function() {
	var tag_name_el = Dase.$('tagName');
	if (tag_name_el) {
		tag_name = tag_name_el.innerHTML;
	}
	var tag_ascii_id_el = Dase.$('tagAsciiId');
	if (tag_ascii_id_el) {
		tag_ascii_id = tag_ascii_id_el.innerHTML;
	}
	var remove_form = Dase.$('removeFromForm');
	var button = Dase.$('removeFromSet');
	if (!button) return;
	var itemSet = Dase.$('itemSet');
	if (!itemSet) return;
	var items = itemSet.getElementsByTagName('input');
	//place the button on the page
	/*
	if (items.length > 3) {
		units = Dase.$('content').clientWidth - Dase.$('itemSet').clientWidth - 45;
		button.style.marginRight =  units+'px';
	}
	*/
	button.onclick = function() {
		var item_uniques_array = [];
		var inputs = itemSet.getElementsByTagName('input');
		if (!inputs.length) return false;
		for (var i=0;i<inputs.length;i++) {
			if ('item_unique[]' == inputs[i].name && true == inputs[i].checked) {
				item_uniques_array[item_uniques_array.length] = encodeURIComponent(inputs[i].value);
			}
		}
		if (!item_uniques_array.length) {
			alert('Please check at least one item.');
			return false;
		}
		if (confirm('Remove '+item_uniques_array.length+' item(s) from '+tag_ascii_id+'?')) {
			var item_uniques = item_uniques_array.join(',');
			var url = Dase.base_href + 'tag/'+Dase.user.eid+'/'+tag_ascii_id+'/items?uniques='+item_uniques;
			Dase.ajax(url,'DELETE',function(resp) {
				alert(resp);
				remove_form.submit();
			});
		}
		return false;
	};
};

Dase.initContentNotes = function() {
	Dase.getNotes();
	var notes = Dase.$('notes');
	var notes_link = Dase.$('notesLink');
	if (!notes_link) return;
	var notesForm = Dase.$('notesForm');
	notes_link.onclick = function() {
		if (notesForm) {
			Dase.toggle(notesForm);
			Dase.$('note').value = '';
		}
		return false;
	};
	notesForm.onsubmit = function() {
		var note = Dase.$('note').value;
		Dase.ajax(document.notes_form.action,'POST',
		function(resp) { 
			Dase.toggle(notesForm);
			Dase.getNotes();
		},note);
		return false;
	};
};

Dase.getNotes = function() {
	var notes = Dase.$('notes');
	var notesLink = Dase.$('notesLink');
	if (!notesLink) return;
	Dase.getJSON(notesLink.href,
	function(resp) {
		var html = '';
		for (var i=0;i<resp.length;i++) {
			html += '<li>'+resp[i].text;
			html += ' <a href="'+notesLink.href+'/'+resp[i].id+'" class="delete note">(x)</a>';
			html += '</li>';
		}
		Dase.$('notes').innerHTML = html;
		var delete_links = notes.getElementsByTagName('a');
		for (var i=0;i<delete_links.length;i++) {
			if ('delete note' == delete_links[i].className) {
				delete_links[i].onclick = function() {
					if (!confirm('delete this note?')) {
						return false;
					}
					Dase.ajax(this.href,'DELETE',
					function(resp) {
						Dase.getNotes();
					},null);
					return false;
				};
			}
		}
	});
};

Dase.initEditLink = function(el) {
	var metadata = Dase.$('metadata');
	var form_div = Dase.$('metadata_form_div');
	if (!metadata) return;
	if (!form_div) return;
	el.onclick = function() {
		Dase.toggle(metadata);
		Dase.toggle(form_div);
		Dase.getJSON(el.href,function(json) {
			//build form and insert it into page
			form_div.innerHTML = Dase.buildForm(json,el.href);
		});
		return false;
	}
};

Dase.initAddMetadata = function()
{
	var mlink = Dase.$('addMetadataLink');
	var mform = Dase.$('addMetadata');
	var coll = Dase.$('collectionAsciiId').innerHTML;
	if (!mlink || !mform) return;
	mlink.onclick = function() {
		if (Dase.toggle(mform)) {
			mform.innerHTML = '<div class="loading">Loading...</div>';
			Dase.getJSON(Dase.base_href + "collection/" + coll + "/attributes",
			function(json){
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
			var input_form = Dase.$('addMetadata').getElementsByTagName('form')[1];
			input_form.onsubmit = function() {
				var content_headers = {
					'Content-Type':'application/x-www-form-urlencoded'
				}
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

Dase.buildForm = function(json,href) {
	var html_form = '<form id="metadata_form" method="post" action="'+href+'">';
	html_form += '<p><input type="submit" value="save changes"></p>';
	for (var i=0;i<json.length;i++) {
		html_form += '<label for="'+json[i].att_ascii_id+'">'+json[i].attribute_name+'</label>';
		/*html_form += '<label for="'+json[i].att_ascii_id+'">'+json[i].html_input_type+'</label>';*/
		html_form += '<p>'+Dase.getFormElement(json[i])+'</p>';
	}
	html_form += '<p><input type="submit" value="save changes"></p>';
	html_form += "</form>";
	return html_form;
};

Dase.getFormElement = function(set) {
	var element_html = '';
	var type = set.html_input_type;
	var name = set.att_ascii_id;
	var value = set.value_text;
	var values = set.values;
	if (value.length > 50) {
		type = 'textarea';
	}
	//NOTE: on form submit, use jquery serialize!
	switch (type) {
		case 'radio':
		break;
		case 'checkbox':
		break;
		case 'select':
		break;
		case 'text': 
		element_html += '<input type="text" name="'+name+'" value="'+value+'" size="'+value.length+'"/>';
		break;
		case 'textarea': 
		element_html += '<textarea name="'+name+'" rows="5">'+value+'"</textarea>';
		break;
		case 'no_edit': 
		element_html += value;
		break;
		case 'listbox': 
		element_html += '<input type="text" name="'+name+'" value="'+value+'" size="'+value.length+'"/>';
		break;
		case 'text_with_menu':
		element_html += '<input type="text" name="'+name+'" value="'+value+'" size="'+value.length+'"/>';
		break;
		default:
		element_html += '<input type="text" name="'+name+'" value="'+value+'" size="'+value.length+'"/>';
	}
	return element_html;
};

//from http://www.quirksmode.org/js/findpos.html
Dase.scrollTo = function (obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
		window.scroll(curleft,curtop);
	}
}


// should probably be in page-specific script:
Dase.initAttributeEdit = function() {
	var att_form;
	var att_form_link = Dase.$('attribute_form_link');
	if (!att_form_link) return;
	Dase.ajax(att_form_link.href,'get',function(txt) {
		att_form = txt;
	});
	var att_data_link = Dase.$('attribute_data_link');
	if (!att_data_link) return;
	var atts_data;
	Dase.getJSON(att_data_link.href,function(json) {
		atts_data = json;
	});
	var table = Dase.$('attributesTable');
	if (!table) return;
	var links = table.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		var classes = links[i].className.split(" ");
		if (classes && classes[1] && 'attribute' == classes[0]) {
			links[i].onclick = function() {
				Dase.loadingMsg(true);
				var att_ascii = this.className.split(" ")[1];
				var editRow = Dase.$('editRow-'+att_ascii);
				Dase.toggle(editRow);
				Dase.scrollTo(this);
				var data = { 'att': atts_data['attributes'][att_ascii]};
				//for select menu
				data.att.ordered_atts = atts_data.ordered_atts;
				data.sort = Dase.$('sort').innerHTML;
				var templateObj = TrimPath.parseTemplate(att_form);
				editRow.innerHTML = templateObj.process(data);
				var d_button = Dase.$('deleteAtt');
				if (d_button) {
					d_button.onclick = function() {
						return confirm('are you sure?');
					};
				}
			 	var def_form = Dase.$('defined_values_form');
				if (def_form) {
					def_form.onsubmit = function() {
						Dase.ajax(def_form.action,'put',function(resp) {
							var jsonObj = JSON.parse(resp);
							Dase.updateDefinedValues(jsonObj);
						},this.defined_values_input.value);
						return false;
					};
				}
				return false;
			};
		}
	}
};

Dase.updateDefinedValues = function(json) {
	var ul = Dase.$('defined_values_list');
	ul.innerHTML = '';
	var inp = Dase.$('defined_values_input');
	inp.value = '';
	var vals;
	for (var i=0;i<json.length;i++) {
		var v = json[i];
		ul.innerHTML += '<li>'+v+'</li>';
		inp.value += v+"\n";
	}
};

//from http://aymanh.com/9-javascript-tips-you-may-not-know#BindingMethodsToObjects
function bind(obj, method) {
	return function() { return method.apply(obj, arguments); }
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

Dase.initShowHtpasswd = function() {
	var link = Dase.$('htpasswdToggle');
	if (!link) return;
	link.onclick = function() {
		Dase.toggle(Dase.$('htpasswd'));
		return false;
	}
}

Dase.getFeedUrl = function() {
	var links = document.getElementsByTagName('link');
	for (var i=0;i<links.length;i++) {
		if (links[i].type == 'application/atom+xml') {
			return links[i].href;
		}
	}
	return false;
}

Dase.getJsonUrl = function() {
	var links = document.getElementsByTagName('link');
	for (var i=0;i<links.length;i++) {
		if (links[i].type == 'application/json') {
			return links[i].href;
		}
	}
	return false;
}

/* generic, declarative form submission confirmation */
Dase.initSubmitConfirm = function() {
	elems = document.getElementsByName('submit_confirm');
	for (var i=0;i<elems.length;i++) {
		elems[i].parentNode.onsubmit = function() {
			return confirm(this.submit_confirm.value);
		}
	}
}

Dase.addLoadEvent(function() {
	Dase.setCollectionAtts();
	Dase.initUser();
	Dase.initMenu('menu');
	Dase.multicheckItems();
	Dase.initToggle();
	Dase.initSaveTo();
	Dase.initRemoveItems();
	Dase.initSubmitConfirm();
	Dase.initLogoff();
	Dase.initContentNotes();
	Dase.initAttributeEdit();
	Dase.initSlideshowLink();
	Dase.initShowHtpasswd();
	if (Dase.pageInit && typeof Dase.pageInit === 'function') {
		Dase.pageInit();
	}
});

