var Dase;
if (Dase && (typeof Dase != "object" || Dase.NAME)) {
	throw new Error("Namespace 'Dase' already exists");
}

// Create our namespace, and specify some meta-information
Dase = {};
Dase.NAME = "Dase";    // The name of this namespace
Dase.VERSION = 1.0;    // The version of this namespace
Dase.user = {};
Dase.base_href = document.getElementsByTagName('base')[0].href;

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
	Dase.getJSON(Dase.base_href + "user/"+eid+ "/data",function(json){
			for (var eid in json) {
			Dase.user.eid = eid;
			Dase.user.name = json[eid].name;
			Dase.user.tags = json[eid].tags;
			Dase.user.collections = json[eid].collections;
			Dase.placeUserName(eid);
			Dase.placeUserTags(eid);
			Dase.placeUserCollections(eid);
			Dase.placeCollectionAdminLink(eid);
			Dase.placeItemEditLink(eid);
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
};

Dase.initLogoff = function() {
	var link = Dase.$('logoff-link');
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
}

Dase.placeItemEditLink = function(eid) {
	//it's not a security check, just a convenience for users
	//with proper credentials to see the edit link
	var auth_info = Dase.checkAdminStatus(eid);
	if (!auth_info) return;
	var edit_link = Dase.$('editLink');
	var controls = Dase.$('adminPageControls');
	if (!edit_link || ('' == edit_link.href)) return;
	if (auth_info.auth_level == 'manager' || auth_info.auth_level == 'superuser' || auth_info.auth_level == 'write')
	{
		Dase.removeClass(controls,'hide');
		Dase.initEditLink(edit_link);
	}
	return;
}

Dase.placeCollectionAdminLink = function(eid) {
	var auth_info = Dase.checkAdminStatus(eid);
	if (!auth_info) return;
	if (auth_info.auth_level == 'manager' || auth_info.auth_level == 'superuser') {
		var menu = Dase.$('menu');
		var li = document.createElement('li');
		li.id = 'admin-menu';
		var a = document.createElement('a');
		a.setAttribute('href','admin/'+auth_info.collection_ascii_id);
		a.className = "main";
		a.appendChild(document.createTextNode(auth_info.collection_name+' Admin'));
		li.appendChild(a);
		menu.appendChild(li);
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
	if (!formDiv) return;
	if (formDiv.hasChildNodes()) {
		//means this is UNchecking the refine box
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

	/* we look in search tallies list for collections */
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

/*Dase.setCollectionAtts = function(coll) {*/
Dase.setCollectionAtts = function() {
	//you can pass in a coll OR use as an event handler
	var coll_el = Dase.$('collectionAsciiId');
	if (coll_el) {
		coll = coll_el.innerHTML;
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

Dase.getItemTallies = function() {
	if (Dase.$("collectionList")) {
		Dase.getJSON(Dase.base_href+"collections/item_tallies", function(json){
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
	var sets = {};
	//this creates the select menu for tags the user can copy to
	var saveChecked = "<select id='saveToSelect' class='plainSelect' name='collection_ascii_id'>";
	saveChecked += "<option value=''>save checked items to...</option>";
	tags.allTags = Dase.$('allTags');
	for (var type in json) {
		if ('user_collection' == type) {
			sets[type] = "<li><a href='new' id='createUserCollection' class='edit'>create new collection</a></li>\n";
		}
		var jsonType = json[type];
		for (var ascii in jsonType) {
			var jsonAscii = jsonType[ascii];
			if ('subscription' != type) {
				saveChecked += "<option value='"+ascii+"'>"+types[type]+": "+jsonAscii+"</option>\n";
			}
			if ('cart' != type) {
				//we populate 'sets' as we go
				if (sets[type]) {
					//concat...
					//note that subscriptions don't REALLY use the ascii_id -- it's actually the tag id
					if ('subscription' == type) {
						var id = ascii.substring(1);
						sets[type] = sets[type] + "<li><a href='tag/" + id  + "'>" + jsonAscii + "</a></li>\n";
					} else {
						sets[type] = sets[type] + "<li><a href='tag/" + eid + "/" + ascii + "'>" + jsonAscii + "</a></li>\n";
					}
				} else {
					//first time through...
					if ('subscription' == type) {
						id = ascii.substring(1);
						sets[type] = "<li><a href='tag/" + id + "'>" + jsonAscii + "</a></li>\n";
					} else {
						sets[type] = "<li><a href='tag/" + eid + "/" + ascii + "'>" + jsonAscii + "</a></li>\n";
					}
				}
			} 
		} 
	}
	saveChecked += "</select>";
	var target = Dase.$('saveChecked');
	//if we are on an item set, also place
	//the select menu for "save checked items to...."
	var item_set = Dase.$('itemSet');
	if (item_set) {
		var items = item_set.getElementsByTagName('td');
	}
	if (target && item_set && items.length) {
		target.innerHTML = saveChecked;
		target.innerHTML += "<input type='submit' value='add'/>";
	}
	for (var type in sets) {
		try{
			Dase.$(type+'-submenu').innerHTML = sets[type];
		} catch(e) {
			//	alert('a friendly notice: ' +e);
		}
	}
	Dase.initCreateNewUserCollection();
};

Dase.initCreateNewUserCollection = function() {
	var createUserCollectionLink = Dase.$('createUserCollection');
	if (createUserCollectionLink) {
		createUserCollectionLink.onclick = function() {
			var tag = {};
			tag.tag_name = prompt("Enter name of collection","");
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
	/* takes attributes json and makes html out of it */
	Dase.getJSON(url,function(json) {
			var html ='<h4>Select Attribute:</h4>';
			html += '<ul id="attList">';
			for (var i=0;i<json.length;i++) {
			var coll_ascii = json[i].collection;
			var att_ascii = json[i].ascii_id;
			var att_name = json[i].attribute_name;
			html += '<li><a href="collection/'+coll_ascii+'/attribute/'+att_ascii+'/values.json" id="'+att_ascii+'" class="att_link '+att_ascii+'"><span class="att_name">'+att_name+'</span> <span class="tally" id="tally-'+att_ascii+'"></span></a></li>';
			}
			html +="</ul></div>";
			Dase.$('attColumn').innerHTML = html;
			Dase.getAttributeTallies(url+'/tallies');
			Dase.bindGetValues(Dase.$('collectionAsciiId').innerHTML);
			});
	var val_coll = Dase.$('valColumn');
	val_coll.className = 'hide';
	val_coll.innerHTML = '';
};

Dase.bindGetValues = function(coll) {
	var atts = Dase.$('attColumn').getElementsByTagName('a');
	for (var i=0;i<atts.length;i++) {
		var att_link = atts[i];
		if (Dase.hasClass(att_link,'att_link')) {
			att_link.onclick = function() {
				var att_name = this.getElementsByTagName('span')[0].innerHTML;
				var att_ascii = this.className.split(" ")[1];
				Dase.getAttributeValues(this.href,att_name,att_ascii,coll,'valColumn');	
				Dase.removeClass(Dase.$('valColumn'),'hide');
				window.scroll(0,0);
				for (var j=0;j<atts.length;j++) {
					Dase.removeClass(atts[j],'spill');
				}
				Dase.addClass(this,'spill');
				return false;
			};
		}
	}
};

Dase.getAttributeValues = function(url,att_name,att_ascii,coll,target) {
	Dase.getJSON(url,function(json) {
			var html ='<h4>Select '+att_name+'</span> Value:</h4>';
			html +="<ul>";
			for (var i=0;i<json.length;i++) {
			var text = json[i].v;
			var tally = json[i].t;
			html +='<li><a href="search?'+coll+'.'+att_ascii+'='+encodeURIComponent(text)+'" class="val_link">'+text+' <span class="tally">('+tally+')</span></a></li>';
			}
			html +="</ul>";
			Dase.$(target).innerHTML = html;
			Dase.getAttributeTallies(url+'/tallies');
			Dase.bindGetValues(Dase.$('collectionAsciiId').innerHTML);
			});
}

Dase.getAttributeTallies = function(url) {
	Dase.getJSON(url,function(json) {
			for(var ascii_id in json) {
			var	att_link = Dase.$(ascii_id);
			if (att_link) {
			//var tally = Dase.$('tally-'+ascii_id).parentNode.getElementsByTagName('span')[0];
			var tally = Dase.$('tally-'+ascii_id);
			if (tally) {
			if (0 == json[ascii_id]) {
			//make admin atts w/ no values disappear
			//tally.parentNode.className = 'hide';
			} else {
			tally.innerHTML = '(' + json[ascii_id] + ')';
			}
			/*
			if (is_admin) {
			// makes admin atts appear ONLY after tallies are set
			Dase.removeClass(Dase.$('get_admin_tallies'),'hide');
			Dase.addClass(Dase.$('get_admin_tallies'),'hide');
			Dase.removeClass(Dase.$('adminAttsLabel'),'hide');
			Dase.removeClass(Dase.$('attList'),'hide');
			}
			*/
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
		url = url + '?' + params + '&cache_buster=' + date.getTime()+'&format=json';
	} else {
		url = url + '?cache_buster=' + date.getTime()+'&format=json';
	}

	xmlhttp.open('GET', url, true);
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
		} else { 	
			// wait for the call to complete
		}
		return false;
	};
};

Dase.initAddToCart = function() {
	var tag_type_data = Dase.$('tag_type');
	if (tag_type_data) {
		var tag_type = tag_type_data.innerHTML;
		//do not display 'add to cart' for user colls & slideshows
		if ('slideshow' == tag_type || 'user_collection' == tag_type) {
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
				item.item_id = inputElem.value;
				HTTP.post(Dase.base_href + 'user/' + Dase.user.eid + "/cart",item,
						function(resp) { 
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
	var sr = Dase.$('itemSet');
	if (!sr) return;
	Dase.getJSON(Dase.base_href + 'user/' + Dase.user.eid + "/cart",
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
		var item_id_array = [];
		var inputs = itemSet.getElementsByTagName('input');
		for (var i=0;i<inputs.length;i++) {
			if ('item_id[]' == inputs[i].name && true == inputs[i].checked) {
				item_id_array[item_id_array.length] = inputs[i].value;
				inputs[i].checked = false;
			}
		}
		if (!item_id_array.length) {
			alert('Please check at least one item.');
			return false;
		}
		if (!tag_ascii_id) {
			alert('Please select a user collection/slideshow/cart to save items to.');
			return false;
		}
		var data = {};
		data.item_ids = item_id_array;
		HTTP.post(Dase.base_href + 'tag/' + Dase.user.eid + "/"+tag_ascii_id,data,
				function(resp) { 
				alert(resp); 
				Dase.initUser();
				Dase.initSaveTo();
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
	if (items.length > 3) {
		units = Dase.$('content').clientWidth - Dase.$('itemSet').clientWidth - 45;
		button.style.marginRight =  units+'px';
	}
	button.onclick = function() {
		var item_id_array = [];
		var inputs = itemSet.getElementsByTagName('input');
		if (!inputs.length) return false;
		for (var i=0;i<inputs.length;i++) {
			if ('item_id[]' == inputs[i].name && true == inputs[i].checked) {
				item_id_array[item_id_array.length] = inputs[i].value;
			}
		}
		if (!item_id_array.length) {
			alert('Please check at least one item.');
			return false;
		}
		if (confirm('Delete '+item_id_array.length+' item(s) from '+tag_ascii_id+'?')) {
			var item_ids = item_id_array.join(',');
			var url = Dase.base_href + 'tag/'+Dase.user.eid+'/'+tag_ascii_id+'/items/'+item_ids;
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

Dase.initAttributeEdit = function() {
	var table = Dase.$('attributesTable');
	if (!table) return;
	var links = table.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		var classes = links[i].className.split(" ");
		if (classes && classes[1] && 'attribute' == classes[0]) {
			links[i].onclick = function() {
				var att_ascii = this.className.split(" ")[1];
				var editRow = Dase.$('editRow-'+att_ascii);
				Dase.getJSON(this.href,function(json) {
						//build form and insert it into page
						//editRow.innerHTML = Dase.buildForm(json,el.href);
						editRow.innerHTML = '<td colspan="0"><h1>'+json.attribute_name+'</h1></td>';
						});
				Dase.toggle(editRow);
				return false;
			};
		}
	}
};


// todo: reorganize these additions once it's clear it's not breaking things.

Function.prototype.bind = function() {
    // http://developer.mozilla.org/en/docs/
    // Core_JavaScript_1.5_Reference:Functions:arguments
    var _$A = function(a){return Array.prototype.slice.call(a);}
    if(arguments.length < 2 && (typeof arguments[0] == "undefined")) return this;
    var __method = this, args = _$A(arguments), object = args.shift();
    return function() {
        return __method.apply(object, args.concat(_$A(arguments)));
    }
}

Dase.util = {};

Dase.util.zip = function() {
    var d = {};
    if(arguments.lenth < 2) return d;
    var arr = arguments[0];
    for(var a = 0; a <= arguments.length; a++) {
        if(a > 0 && arr[a - 1]) d[arguments[a]] = arr[a-1];
    }
    return d;
}

Dase.widget = {};

Dase.addLoadEvent(function() {
		Dase.setCollectionAtts();
		Dase.initUser();
		Dase.initMenu('menu');
		Dase.initBrowse();
		Dase.multicheckItems();
		Dase.initToggle();
		Dase.initSaveTo();
		Dase.initRemoveItems();
		Dase.initLogoff();
		Dase.initContentNotes();
		Dase.initAttributeEdit();
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

