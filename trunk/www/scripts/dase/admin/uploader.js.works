Dase.widget.uploader = function(form_, iframe_, status_, onload, onsend) {
	this._form = form_;
	this._input = this._form.getElementsByTagName('input')[0];
	$(this._input).bind('change', (function() {
		this.send();
	}).bind(this));
	this._iframe = iframe_;
	$(this._iframe).hide();
	this._status = status_;
	this.onload = onload;
	this.onsend = onsend;
	this._form.target = this._iframe.id;

	return;
};

Dase.widget.uploader.init = function(e, onload, onsend) {
	if(e.tagName) {
		container = e;
		e = {
			form_: $(container).children('form')[0],
			iframe_: $(container).children('iframe')[0],
			status_: $(container).children('.status')[0]
		}
	}
	var du = new Dase.widget.uploader(e.form_, e.iframe_, e.status_);
	du.onload = onload;
	du.onsend = onsend;
	return du;
}

Dase.widget.uploader.prototype.parse_response = function(response) {
	return JSON.parse(response);
}

Dase.widget.uploader.prototype.send = function() {
	$(this._iframe).one('load', (function() {
		var response = this._iframe.contentDocument.body.innerHTML;
		//debug
		//alert(response);
		var r = this.parse_response(response);
		this.onload(r, this);
	}).bind(this))
	this._form.submit();
	if(this.onsend) this.onsend(this);
}

Dase.widget.messagequeue = function(parent_, grow_method) {
	if(grow_method != this.STACK && grow_method != this.APPEND){
		var grow_method = this.STACK;
	}
	this.grow_method = grow_method
	this.container = document.createElement('div');
	$(this.container).addClass('widget').addClass('messagequeue');
	this.list = document.createElement('ul');
	$(this.container).append(this.list);
	if(parent_) $(parent_).append(this.container);
}

Dase.widget.messagequeue.prototype.STACK = 'prepend';
Dase.widget.messagequeue.prototype.APPEND = 'append';

Dase.widget.messagequeue.prototype.push = function(message, classname) {
	var el = document.createElement('li');
	$(el).addClass(classname).append(message);
	if(this.grow_method == this.APPEND) $(this.list).append(el);
	else $(this.list).prepend(el);
}

Dase.widget.messagequeue.prototype.pop = function() {
	if(!this.list.hasChildren) return false;
	if(this.grow_method == this.APPEND) return $(this.list.lastChild).remove();
	else return $(this.list.firstChild).remove();
}

Dase.widget.messagequeue.prototype.unshift = function(message, classname) {
	var el = document.createElement('li');
	$(el).addClass(classname).append(message);
	if(this.grow_method == this.STACK) $(this.list).append(el);
	else $(this.list).prepend(el);
}

Dase.widget.messagequeue.prototype.shift = function() {
	if(!this.list.hasChildren) return false;
	if(this.grow_method == this.STACK) return $(this.list.lastChild).remove();
	else return $(this.list.firstChild).remove();
}

Dase.pageInit = function() {
	result_queue = new Dase.widget.messagequeue(document.getElementById('results'));
	for (var i=0;i<document.forms.length;i++) {
		document.forms[i].reset();
	}
	for (var j=1;j<11;j++) {
		var up = Dase.$('uploader_'+j);
		if (up) {
			Dase.widget.uploader.init(
				up, 
				function(parsed_result, uploader) {
					var num = parsed_result.num;
					if(parsed_result.status == 'ok') {
						if(parsed_result.filesize > 1000000) {
							var filesize = Math.ceil(parseInt(parsed_result.filesize)/1000000)+'MB';
						} else {
							var filesize = Math.ceil(parseInt(parsed_result.filesize)/1000)+'KB';
						}
						var message = parsed_result.filename+' ['+filesize+']';
						message +='<a href="'+parsed_result.item_url+'">'+parsed_result.title+'</a>';
						message += ' <img src="'+parsed_result.thumbnail_url+'"/>';
					} else {
						var message = parsed_result.message;
						if (parsed_result.filename) {
							message = message + ': ' + parsed_result.filename;
						}
						if (parsed_result.filesize) {
							message = message + ' [' + parsed_result.filesize + ']';
						}
					}
					result_queue.push(message, parsed_result.status);
					Dase.addClass(Dase.$('queue_'+num),'hide');
				},
				function(uploader) {
					var num = uploader._form.num.value;
					Dase.addClass(Dase.$('uploader_'+num),'hide');
					Dase.createElem(Dase.$('queue_'+num),' '+uploader._input.value,'span');
					var link = Dase.createElem(Dase.$('queue_'+num),' [x]','a');
					link.onclick = function() {
						Dase.addClass(Dase.$('queue_'+num),'hide');
						return false;
					}
					link.className="delete";
					link.href="#";

					Dase.removeClass(Dase.$('queue_'+num),'hide');
					pos = parseInt(num)+1;
					Dase.removeClass(Dase.$('uploader_'+pos),'hide');
					Dase.removeClass(Dase.$('uploader_'+pos+'_form'),'hide');
				}
				)
			}
		}
	};

