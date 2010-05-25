Dase.deleteItems = {};

Dase.deleteItems.multicheck = function(c) { 
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

Dase.deleteItems.deleteUri = function(url,img,a,htuser,htpasswd) {
	Dase.ajax(url,'DELETE',function(resp) {
		Dase.addClass(img,'hide');
		if ('item deleted' == resp) {
			a.href = resp;
			a.className = 'hide';
		} else {
			alert(resp);
		}
	},null,htuser,htpasswd,null,function(resp) {
		Dase.addClass(img,'hide');
		alert('sorry, there was a problem');
	});
}

Dase.deleteItems.initForm = function() {
	var eid = Dase.getEid();
	var form = Dase.$('deleter');
	if (!form) return;
	form.onsubmit = function() {
		var htuser = Dase.user.eid;
		//todo: how does it get htpasswd?
		var htpasswd = Dase.user.htpasswd;
		var list = Dase.$('fileList');
		var files = list.getElementsByTagName('a');
		for (var i=0;i<files.length;i++) {
			var inp = files[i].parentNode.getElementsByTagName('input')[0];
			if (true == inp.checked && htuser && htpasswd) {
				Dase.addClass(inp,'hide');
				inp.checked = false;
				files[i].className = 'pending';
				var img = files[i].parentNode.getElementsByTagName('img')[0];
				Dase.removeClass(img,'hide');
				var url = files[i].href;
				Dase.deleteItems.deleteUri(url,img,files[i],htuser,htpasswd);
			}
		}
		return false;
	};
};

Dase.pageInitUser = function() {
	Dase.deleteItems.multicheck('checked');
	Dase.deleteItems.initForm();
};

