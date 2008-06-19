/* Requires jQuery */

Dase.widget.uploader = function(form_, iframe_, status_, callback) {
    this._form = form_;
    this._input = this._form.getElementsByTagName('input')[0];
    $(this._input).bind('change', (function() {
        this.send();
    }).bind(this));
    this._iframe = iframe_;
    $(this._iframe).hide();
    this._status = status_;
    this.callback = callback;
    this._form.target = this._iframe.id;

    return;
};

Dase.widget.uploader.prototype.parse_response = function(response) {
    return Dase.util.zip(response.split('|'),
        'status',
        'message',
        'filename',
        'filesize',
        'filetype',
        'href'
    );
}

Dase.widget.uploader.prototype.send = function() {
    $(this._iframe).one('load', (function() {
        var response = this._iframe.contentDocument.body.innerHTML;
        //alert(this.parse_response(response).toSource());
        var r = this.parse_response(response);
        this.callback(r, this);
    }).bind(this))

    this._input.disabled = true;
 
    this._form.submit();
}

Dase.widget.uploader.init = function(e, callback) {
    if(e.tagName) {
        container = e;
        e = {
            form_: $(container).children('form')[0],
            iframe_: $(container).children('iframe')[0],
            status_: $(container).children('.status')[0]
        }
    }
    var du = new Dase.widget.uploader(e.form_, e.iframe_, e.status_);
    du.callback = callback;
    return du;
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
