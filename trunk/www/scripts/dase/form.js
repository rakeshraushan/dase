Dase.form = function() {
    return {
        getByName: function(name) {
            for(f in document.forms) {
                if(document.forms[f].name == name) return document.forms[f];
            }
            return false;
        },
        serialize: function(f, formatter) {
            if(f instanceof HTMLFormElement) var form = f;
            else var form = Dase.form.getByName(f) || document.getElementById(f);
            if(!(form instanceof HTMLFormElement)) {
                throw new TypeError('Not a valid form name or id: '+f);
            }
            formatter = typeof formatter == 'function' || this.postify;
            var element_handlers = {
                'input': function(el) { return el.type; },
                'select': function(el) { return 'select'; },
                'textarea': function(el) { return 'textarea'; }
            }
            var type_handlers = {
                'text': function(el) { return el.value; },
                'hidden' : function(el) { return el.value; },
                'textarea': function(el) { return el.value; },
                'select': function(el) { 
                    return Dase.util.map(filter(el.options, function(o) {
                        return o.selected; 
                    }), function(el) {
                            return el.value; 
                        }); 
                },
                'radio': function(el) { return el.checked ? el.value : null; },
                'checkbox': function(el) { return el.checked ? el.value : null; }
            }
            var params = {};
            for(c in form.childNodes) {
                n = form.childNodes[c];
                if(n.nodeType && n.nodeType == 1) {
                    if(n.tagName && n.name && element_handlers[n.tagName.toLowerCase()] && type_handlers[element_handlers[n.tagName.toLowerCase()](n)]) {
                        if(params[n.name] && !(params[n.name] instanceof Array)) {
                            params[n.name] = [params[n.name]];
                         }

                         if(params[n.name]) {
                            params[n.name].push(type_handlers[element_handlers[n.tagName.toLowerCase()](n)](n));
                         } else {
                            params[n.name] = type_handlers[element_handlers[n.tagName.toLowerCase()](n)](n);
                         }
                    }
                }
            }
            return formatter(Dase.util.ofilter(params, function(k,v) { return v != null; }));
        },
        postify: function(obj) {
            var params = [];
            for(k in obj) {
                if(obj[k] instanceof Array) {
                    var multivalue = [];
                    for(kk in obj[k]) {
                        if(obj[k][kk] != null) params.push(escape(k.toString())+'='+escape(obj[k][kk].toString()));
                    }
                } else {
                    if(obj[k] != null) params.push(escape(k.toString())+'='+escape(obj[k].toString()));
                }
            }
            return params.join('&');
        }
    }
}();
