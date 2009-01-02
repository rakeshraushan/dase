if (!Dase || typeof Dase != "object") {
	throw new Error("Namespace 'Dase' does not exists");
}

Dase.atompub = {};
Dase.atom = {};
Dase.atompub.ns = "http://www.w3.org/2007/app";
Dase.atom.ns = "http://www.w3.org/2005/Atom";

//from http://www.hesido.com/web.php?page=atomidtimestamp
function padzero(toPad) {
	return (toPad+'').replace(/(^.$)/,'0$1');
}

function paddoublezero(toPad) {
	return (toPad+'').replace(/(^.$)/,'0$1').replace(/(^..$)/,'0$1');
}


Dase.atompub.getDate = function() {
	var d = new Date();
	var utcf = d.getTimezoneOffset();
	var ypad = d.getFullYear();
	var ypadutc = d.getUTCFullYear();
	var mpad = padzero(d.getMonth()+1);
	var mpadutc = padzero(d.getUTCMonth()+1);
	var dpad = padzero(d.getDate());
	var dpadutc = padzero(d.getUTCDate());
	var SHour = padzero(d.getHours());
	var SHourutc = padzero(d.getUTCHours());
	var SMins = padzero(d.getMinutes());
	var SMinsutc = padzero(d.getUTCMinutes());
	var SSecs = padzero(d.getSeconds());
	var SSecsutc = padzero(d.getUTCSeconds());
	var SMlsc = paddoublezero(d.getMilliseconds());
	var SMlscutc = paddoublezero(d.getUTCMilliseconds());
	var uOhr = padzero(Math.floor(Math.abs(utcf) / 60));
	var uOmn = padzero(Math.abs(utcf) - Math.floor(Math.abs(utcf) / 60) * 60);
	//switched plus & minus here --pkeane 20081225
	var oFsg = (utcf < 0) ? '+' : '-';
	return ypad+'-'+mpad+'-'+dpad+'T'+SHour+':'+SMins+':'+SSecs+oFsg+uOhr+':'+uOmn;
}

Dase.atompub.getEditLink = function() {
	var links = document.getElementsByTagName('link');
	for (var i=0;i<links.length;i++) {
		if ('edit' == links[i].rel) {
			return links[i].href;
		}
	}
};

Dase.atompub.getJsonEditLink = function() {
	var links = document.getElementsByTagName('link');
	for (var i=0;i<links.length;i++) {
		if ('http://daseproject.org/relation/edit' == links[i].rel) {
			return links[i].href;
		}
	}
};

Dase.atompub.getAtom = function(url,my_func,username,password) {
	var xmlhttp = Dase.createXMLHttpRequest();
	// this is to deal with IE6 cache behavior
	var date = new Date();
	url = url + '?cache_buster=' + date.getTime();
	xmlhttp.open('get',url,true);
	if (username && password) {
		xmlhttp.setRequestHeader('Authorization','Basic '+Base64.encode(username+':'+password));
	}
	xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200 && xmlhttp.responseXML) {
				var atom = xmlhttp.responseXML;
				if (my_func) {
					my_func(atom);
				} 
			}
		} 
		return false;
	};
};

Dase.atompub.putAtom = function(url,xml_obj,my_func,username,password) {
	//work on this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	var xmlhttp = Dase.createXMLHttpRequest();
	// this is to deal with IE6 cache behavior
	var date = new Date();
	url = url + '?cache_buster=' + date.getTime();
	xmlhttp.open('put',url,true);
	if (username && password) {
		xmlhttp.setRequestHeader('Authorization','Basic '+Base64.encode(username+':'+password));
	}
	var atom = Dase.atompub.serialize(xml_obj);
	xmlhttp.setRequestHeader("Content-Type",'application/atom+xml;type=entry; charset=UTF-8;'),
	xmlhttp.setRequestHeader('Content-MD5',hex_md5(atom));
	xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200 && xmlhttp.responseText) {
				if (my_func) {
					my_func(xmlhttp.responseText);
				} 
			}
		} 
		return false;
	};
};

//from rhino book p.520
Dase.atompub.serialize = function(xml_obj) {
	if (typeof XMLSerializer != "undefined") {
		return (new XMLSerializer()).serializeToString(xml_obj);
	} else if (xml_obj.xml) {
		return xml_obj.xml;
	} else {
		throw "XML.serialize is not supported or cannot serialize " + xml_obj;
	}
}

Dase.atom.entry = function(title) {
	var doc =  XML.newDocument('atom:entry',Dase.atom.ns);
	var title_elem = doc.createElementNS(Dase.atom.ns, "atom:title");
	title_elem.appendChild(doc.createTextNode(title));
	//var root = doc.getElementsByTagName('atom:entry')[0];
	var root = doc.firstChild;
	root.appendChild(title_elem);
	var updated_elem = doc.createElementNS(Dase.atom.ns, "atom:updated");
	updated_elem.appendChild(doc.createTextNode(Dase.atompub.getDate()));
	root.appendChild(updated_elem);
	var author_elem = doc.createElementNS(Dase.atom.ns, "atom:author");
	var name_elem = doc.createElementNS(Dase.atom.ns, "atom:name");
	author_elem.appendChild(name_elem);
	root.appendChild(author_elem);
	var content_elem = doc.createElementNS(Dase.atom.ns, "atom:content");
	content_elem.setAttribute('type','text');
	content_elem.appendChild(doc.createTextNode('just some text'));
	root.appendChild(content_elem);
	return doc;
}; 


//demo function
Dase.atompub.showResource = function() {
	var url = Dase.atompub.getJsonEditLink();
	if (!url) return;
	Dase.getJSON(url,function(json) {
		var data = {'atom':json};
		var templateObj = TrimPath.parseDOMTemplate("atom_jst");
		var atom = Dase.util.trim(templateObj.process(data));
	},Dase.user.eid,Dase.user.htpasswd);
};


Dase.addLoadEvent(function() {
	//Dase.atompub.showResource();
});