Dase.htmlbuilder = function(tagName,keyvals,text) {
	this.tagName = tagName || ''; //no tagname means container
	this.keyvals = keyvals || {};
	this.text = text || '';
	this.elements = [];
	this.attach = function(target,append_flag) {
		var result = [];
		if (append_flag) {
			target.innerHTML += Dase.htmlbuilder.buildstring(this,result).join('');
		} else {
			target.innerHTML = Dase.htmlbuilder.buildstring(this,result).join('');
		}
	};
	this.getString = function(target) {
		var result = [];
		return Dase.htmlbuilder.buildstring(this,result).join('');
	};
	return this;
};

Dase.htmlbuilder.buildstring = function(el,result) {
//	alert(JSON.stringify(el));
	if (el.tagName) {
		result.push('<'+el.tagName);
		if (el.keyvals) {
			for (var key in el.keyvals) {
				result.push(' '+key+'="'+el.keyvals[key]+'"');
			}
		}
		if (el.elements.length) {
			result.push('>\n');
		} else {
			if (!el.text) {
				//close tag
				result.push('/>');
				return result;
			}
			result.push('>');
		}
	}
	for (var i=0;i<el.elements.length;i++) {
		Dase.htmlbuilder.buildstring(el.elements[i],result);
	}
	if (el.tagName) {
		result.push(el.text+'</'+el.tagName+'>\n');
	}
	return result;
};

Dase.htmlbuilder.prototype.set = function(key,val) {
	this.keyvals[key] = val;
};

Dase.htmlbuilder.prototype.setText = function(text) {
	this.text = text;
};

Dase.htmlbuilder.prototype.add = function(tagName,keyvals,text) {
	var h = new Dase.htmlbuilder(tagName,keyvals,text);
	this.elements.push(h);
	return h;
};
