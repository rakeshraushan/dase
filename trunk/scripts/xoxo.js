if (!Dase) { var Dase = {}; }

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
		}
	}
}

Dase.$ = function(id) {
	return document.getElementById(id);
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


Dase.toggle = function(el) {
	if (Dase.hasClass(el,'hide')) {
		Dase.removeClass(el,'hide');
	} else {
		Dase.addClass(el,'hide');
	}
}

Dase.initXoxo = function() { 
	var menu = Dase.$('xoxo');
	if (menu) {
		var listItems = menu.getElementsByTagName('li');
		for (var i=0;i<listItems.length;i++) {
			var listItem = listItems[i];
			var listItemLink = listItem.getElementsByTagName('a')[0];
			if (listItemLink) {
				var toggled = listItemLink.nextSibling;
				//need to make case insensitive??
				if ('UL' == toggled.tagName) {
					listItemLink.onclick = function() {
						var child_ul = this.parentNode.getElementsByTagName('ul')[0];
						if (child_ul) {
							Dase.toggle(child_ul);
							return false;
						} else {
							return true;
						}
					}
				}
				//need to make case insensitive??
				if ('DL' == toggled.tagName) {
					listItemLink.onclick = function() {
						var child_dl = this.parentNode.getElementsByTagName('dl')[0];
						if (child_dl) {
							Dase.toggle(child_dl);
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


Dase.addLoadEvent(function() {
		Dase.initXoxo();
		});

