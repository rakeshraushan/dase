var Dase;
if (Dase && (typeof Dase != "object" || Dase.NAME)) {
	throw new Error("Namespace 'Dase' already exists");
}

// Create our namespace, and specify some meta-information
Dase = {};
Dase.NAME = "Dase";    // The name of this namespace
Dase.VERSION = 1.0;    // The version of this namespace
Dase.user = {};
Dase.registry = {};
Dase.base_href = document.getElementsByTagName('base')[0].href;
Dase.htmlInputTypes = {};
Dase.htmlInputTypeLabel = {};
Dase.htmlInputTypes.RADIO = 1;
Dase.htmlInputTypes.CHECKBOX = 2;
Dase.htmlInputTypes.SELECTMENU = 3;
Dase.htmlInputTypes.DYNAMICBOX = 4;
Dase.htmlInputTypes.TEXT = 5;
Dase.htmlInputTypes.TEXTAREA = 6;
Dase.htmlInputTypes.LISTINPUT = 7;
Dase.htmlInputTypes.NOEDIT = 8;
Dase.htmlInputTypeLabel[1] = "Radio Buttons";
Dase.htmlInputTypeLabel[2] = "Checkboxes";
Dase.htmlInputTypeLabel[3] = "Select Menu";
Dase.htmlInputTypeLabel[4] = "Text Box w/ Menu";
Dase.htmlInputTypeLabel[5] = "Text Box";
Dase.htmlInputTypeLabel[6] = "Text Area";
Dase.htmlInputTypeLabel[7] = "List Input";
Dase.htmlInputTypeLabel[8] = "Non-Editable";

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
	if (!jsalert) return false;
	Dase.removeClass(jsalert,'hide');
	jsalert.innerHTML = '';
	jsalert.innerHTML = msg;
	return true;
};

Dase.toggle = function(el) {
	if (Dase.hasClass(el,'hide')) {
		Dase.removeClass(el,'hide');
	} else {
		Dase.addClass(el,'hide');
	}
};

//note: this isn't used anymore:
Dase.formatDate = function() {
	var d = new Date();
	var mo;
	var da;
	if (1 == String(d.getMonth()+1).length) {
		mo = "0" + String(d.getMonth()+1);
	} else {
		mo = String(d.getMonth()+1);
	} 
	if (1 == String(d.getDate()).length) {
		da = "0" + String(d.getDate());
	} else {
		da = String(d.getDate());
	} 
	return String(d.getFullYear()) + mo + da; 
}

Dase.createListFromObj = function(parent,set,cName) {
	parent.className = 'show';
	var list = document.createElement('ul');
	parent.appendChild(list);
	if (cName) {
		list.setAttribute('class',cName);
	}
	for (var member in set) {
		var item = document.createElement('li');
		item.appendChild(document.createTextNode(member+' '+set[member]));
		list.appendChild(item);
		if (set[member] instanceof Object) {
			Dase.createListFromObj(item,set[member]);
		}
	}
};

Dase.createHtmlSet = function(parent,set,tagName) {
	for (var i=0;i<set.length;i++) {
		Dase.createElem(parent,set[i],tagName);
	}
};

Dase.createElem = function(parent,value,tagName,className) {
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
	element.style.visibility = 'visible';
	return element;
};

Dase.createCheckbox = function(parent,value,name) {
	/*
	var element = document.createElement('input');
	parent.appendChild(element);
	//from http://alt-tag.com/blog/archives/2006/02/ie-dom-bugs/
	//WORK ON THIS!
	try {
		element.setAttribute("type", "checkbox");
	} catch (e) {
		var newElement = null;
		var tempStr = element.getAttribute("name");
		try {
			newElement = document.createElement("<input type=\"" +typeStr+ "\" name=\"" +tempStr+ "\">");
		} catch (e) {}
		if (!newElement) {
			newElement = document.createElement("input");
			newElement.setAttribute("type", "checkbox");
			newElement.setAttribute("name", tempStr);
		}
		if (tempStr = element.getAttribute("value")) {
			newElement.setAttribute("value", tempStr);
		}
		element.parentNode.replaceChild(newElement, element);
	}
	*/
};

Dase.removeChildren = function(target) {
	if (!target) return;
	while (target.childNodes[0]) {
		target.removeChild(target.childNodes[0]);
	}
}

Dase.highlight = function(target,time) {
	Dase.addClass(target,'highlight');
	setTimeout(function() {
			Dase.removeClass(target,'highlight');
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

Dase.initWidget = function(widget,hooks) {
	var ph = Dase.$('pageHook').innerHTML;
	for (var i=0;i<hooks.length;i++) {
		if (ph == hooks[i]) {
			widget.run();
		}
	}
}

Dase.registerWidget = function(widget,hooks) {
	Dase.addLoadEvent(function() {
			Dase.initWidget(widget,hooks);
			});
}

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
			Dase.checkAdminStatus(eid);
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

Dase.checkAdminStatus = function(eid) {
	var current_coll_elem = Dase.$('collectionAsciiId');  
	if (!current_coll_elem) return;
	var current_coll = current_coll_elem.innerHTML;  
	for (var i=0;i<Dase.user.collections.length;i++) {
		var c = Dase.user.collections[i];
		//display link to administer collection is user has privs
		if (current_coll && (c.ascii_id == current_coll)  
				&& ((c.auth_level == 'manager') || (c.auth_level == 'superuser'))
		   ) {
			var menu = Dase.$('menu');
			var li = document.createElement('li');
			li.setAttribute('class','admin');
			li.setAttribute('className','admin');
			var a = document.createElement('a');
			a.setAttribute('href','admin/'+eid+'/'+c.ascii_id);
			a.setAttribute('class','main');
			a.setAttribute('className','main');
			a.appendChild(document.createTextNode(c.collection_name+' Admin'));
			li.appendChild(a);
			menu.appendChild(li);
		}
	}
}

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
			a.setAttribute('href','collection/'+c.ascii_id);
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
		//this simply shows the "Special Access Collections" subhead
		Dase.removeClass(Dase.$('specialAccessLabel'),'hide');
	}
};

Dase.placeUserSearchCollections = function() {
	//this is the selector of all of the collections
	//the user is allowed to search
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

	var st = Dase.$('searchTallies');
	var coll_tallies = st.getElementsByTagName('li');
	var colls_array = [];
	for (var i=0;i<coll_tallies.length;i++) {
		colls_array.push(coll_tallies[i].className);
	}
	if (1 == colls_array.length) {
		var collection = colls_array[0];
		var hidden = document.createElement('input');
		hidden.setAttribute('type','hidden');
		hidden.setAttribute('name','collection_ascii_id');
		hidden.setAttribute('value',collection);
		formDiv.appendChild(hidden);
		Dase.limitSearchToCollection([collection]);
	} else {
		Dase.limitSearchToCollection(colls_array);
	}
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
		}
	}
};

Dase.limitSearchToCollection = function(c_ascii_array) {
	//remove all collection options except for
	//those in c_ascii_array
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
		Dase.getJSON(Dase.base_href + "json/collection/" + coll + "/attributes",function(json){
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

/*
Dase.initCheckImage = function() { 
	Dase.marked = new Array();
	var thumbs = Dase.$('searchResults');
	if (!thumbs) { return; }
	var formParent = Dase.$('saveMarkedToCollection');
	var form = Dase.createElem(formParent,null,form);
	var boxes = thumbs.getElementsByTagName('input');
	for (var i=0; i<boxes.length; i++) {
		boxes[i].onclick = function() {
			var thumbTd = this.parentNode.parentNode;
			var itemId = this.value;
			var target = Dase.$('saveMarkedToCollection');
			//var title = thumbTd.getElementsByTagName('h4')[0].innerHTML;
			//	Dase.createElem(target,title,'li','item_'+itemId);
			if (Dase.hasClass(thumbTd,'checked')) {
				Dase.removeClass(thumbTd,'checked');
				Dase.removeFromArray(Dase.marked,itemId);
			} else {
				Dase.marked[Dase.marked.length] = itemId;
				Dase.addClass(thumbTd,'checked');
			}
			target.innerHTML = Dase.marked.length;
		};
	}	   
};
*/

Dase.multicheckItems = function(className) {
	if (!className) {
		className = 'check';
	}
	var item_set = Dase.$('itemSet');
	if (!item_set)  return; 
	/* creates and initializes list check/uncheck toggle 
	 */
	var multi = document.createElement('a');
	multi.setAttribute('href','');
	multi.className = className;
	multi.appendChild(document.createTextNode('check/uncheck all'));
	target = Dase.$('checkItems');
	target.innerHTML = '';
	var boxes = item_set.getElementsByTagName('input');
	if (boxes.length > 0) {
		target.appendChild(multi);
	}
	multi.onclick = function() {
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
		setTimeout('Dase.loadingMsg(false)',1500);
	} else {
		var loading = Dase.$('ajaxMsg');
		loading.innerHTML = '';
	}
}

Dase.placeUserTags = function(eid) {
	var types = {
		'cart':'My Cart',
		'subscription':'Subscription',
		'slideshow':'Slideshow',
		'user_collection':'User Collection'
	};
	var json = Dase.user.tags;
	var tags={};
	var cart_tally = 0;
	var sets = {};
	var saveToSelector = "<select class='plainSelect' name='collection_ascii_id'>";
	saveToSelector += "<option value=''>save checked items to...</option>";
	tags.allTags = Dase.$('allTags');
	for (var type in json) {
		var jsonType = json[type];
		for (var ascii in jsonType) {
			var jsonAscii = jsonType[ascii];
			if ('subscription' != type) {
				saveToSelector += "<option value='"+ascii+"'>"+types[type]+": "+jsonAscii+"</option>\n";
			}
			if ('cart' != type) {
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
				cart_tally = jsonType[ascii];
			}
		} 
	}
	saveToSelector += "</select>";
	//the select menu for "save checked items to...."
	var target = Dase.$('saveToSelector');
	var item_set = Dase.$('itemSet');
	if (item_set) {
		var items = item_set.getElementsByTagName('td');
	}
	if (target && item_set && items) {
		target.innerHTML = saveToSelector;
		target.innerHTML += "<input type='submit' value='add'/>";
	}
	for (var type in sets) {
		try{
			Dase.$(type).innerHTML = sets[type];
		} catch(e) {
		//	alert('a friendly notice: ' +e);
		}
	}
	var label = Dase.$('cartLabel');
	if (label) {
		label.innerHTML += " ("+cart_tally+")";
	}
	//UNsuccessful attempt to fix ie bug
	//label.parentNode.style.display = 'inline';

	//UNsuccessful attempt to fix ie bug
	//see ie7-squish.js,ie7-recalc.js
	//document.recalc();
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
			Dase.bindGetValues(Dase.$('collectionAsciiId').innerHTML);
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
		url = "json/collection/" + coll + "/admin_attribute_tallies";
		is_admin = 1;
	}
	if (Dase.$('get_public_tallies')) {
		url = "json/collection/" + coll + "/attribute_tallies";
	}
	if (Dase.$('get_cb_tallies')) {
		url = "json/collection/" + coll + "/cb_attribute_tallies";
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
			if (Dase.$(elem_id)) {
				Dase.$(elem_id).innerHTML = returnStr;
			}
			if (my_func) {
				my_func();
			}
		} else {
			// wait for the call to complete
		}
	};
};

Dase.ajax = function(url,method,my_func,msgBody) {
	if (!method) {
		method = 'POST';
	}
	var xmlhttp = Dase.createXMLHttpRequest();
	xmlhttp.open(method, url, true);
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
		} else {
			// wait for the call to complete
		}
		if (xmlhttp.readyState == 4 && xmlhttp.status != 200) {
			alert(xmlhttp.responseText);
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

Dase.getJSON = function(url,my_func,error_func,params) {
	var xmlhttp = Dase.createXMLHttpRequest();
	// this is to deal with IE6 cache behavior
	// also note that JSON data needs to be up-to-the-second
	// accurate given the way we currently do deletes!
	var date = new Date();
	if (params) {
		url = url + '?' + params + '&' + date.getTime();
	} else {
		url = url + '?' + date.getTime();
	}

	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200 && xmlhttp.responseText) {
				//alert(xmlhttp.responseText);
				var jsonObj = JSON.parse(xmlhttp.responseText);
				var json = jsonObj.json;
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
			var label = Dase.$('cartLabel');
			if (label) {
			if (undefined !== json.length) {
			label.innerHTML = "My Cart ("+json.length+")";
			} else {
			label.innerHTML = "My Cart (" + 0 + ")";
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
		Dase.multicheckItems();
//		Dase.initCheckImage();
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

