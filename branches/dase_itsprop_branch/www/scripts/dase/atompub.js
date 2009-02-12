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


Dase.atompub.putJson = function(url,json_obj,my_func,user,pass) {
	//alert(JSON.stringify(json_obj));
	var data = {'atom':json_obj};
	var templateObj = TrimPath.parseDOMTemplate("atom_jst");
	var atom = Dase.util.trim(templateObj.process(data));
	//alert(atom);
	var headers = {
		'Content-Type':'application/atom+xml;type=entry'
	}
	Dase.ajax(url,'put',function(resp) { 
		my_func(resp);
	},atom,user,pass,headers,function(error) {
		alert(error);
	}); 
}

Dase.atom.jsonEntry = function(title,entrytype,categories) {
	var atom = {};
	var d = new Date();
	atom.id = 'tag:daseproject.org,2008:'+d.valueOf();
	atom.title = title;
	atom.updated = Dase.atompub.getDate();
	atom.author_name = 'daseproject';
	atom.entrytype = entrytype;
	atom.content = {};
	atom.rights = 'internal';
	atom.content.text = 'abcd';
	atom.content.type = 'text';
	if (categories) {
		atom.category = categories;
	}
	return atom;
};


Dase.addLoadEvent(function() {
});
