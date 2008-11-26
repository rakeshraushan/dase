Dase.webspace = {};

Dase.webspace.multicheck = function(c) { 
	var list = Dase.$('fileList');
	if (!list) { return; }
	target = Dase.$('checkall');
	if (!target) { return; }
	//class of the link determines its behaviour
	target.className = 'uncheck';
	var boxes = list.getElementsByTagName('input');
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

Dase.webspace.postUri = function(payload_url,img,a,span,coll,htuser,htpasswd) {
	var content_headers = {
		'Content-Type':'text/uri-list'
	}
	url = 'https://daseupload.laits.utexas.edu/collection/'+coll+'/ingester';
	Dase.ajax(url,'POST',function(resp) {
		a.href = resp;
		a.className = 'uploaded';
		span.innerHTML = 'uploaded '+span.innerHTML; 
		Dase.addClass(img,'hide');
	},payload_url,htuser,htpasswd,content_headers,function() {
		Dase.addClass(img,'hide');
		span.innerHTML = 'sorry, upload did not succeed';
	});
}

Dase.webspace.initForm = function() {
	var form = Dase.$('ingester');
	if (!form) return;
	form.onsubmit = function() {
		htuser = Dase.user.eid;
		htpasswd = Dase.user.htpasswd;
		coll = Dase.$('collectionAsciiId').innerHTML;
		Dase.addClass(Dase.$('checker'),'hide');
		Dase.addClass(Dase.$('submitButton'),'hide');
		var list = Dase.$('fileList');
		var files = list.getElementsByTagName('a');
		for (var i=0;i<files.length;i++) {
			var span = files[i].parentNode.getElementsByTagName('span')[0];
			var inp = files[i].parentNode.getElementsByTagName('input')[0];
			Dase.addClass(inp,'hide');
			var img = files[i].parentNode.getElementsByTagName('img')[0];
			Dase.removeClass(img,'hide');
			var payload_url = files[i].href;
			Dase.webspace.postUri(payload_url,img,files[i],span,coll,htuser,htpasswd);
		}
		return false;
	}
}

Dase.pageInit = function() {
	Dase.webspace.multicheck('checked');
	Dase.webspace.initForm();
}

