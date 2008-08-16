Dase.form = function() {
    return {
        getByName: function(name) {
            for(f in document.forms) {
                if(document.forms[f].name == name) return document.forms[f];
            }
            return false;
        },
        serialize: function(f) {
            if(f instanceof HTMLFormElement) var form = f;
            else var form = Dase.form.getByName(f) || document.getElementById(f);
            if(!(form instanceof HTMLFormElement)) {
                throw new TypeError('Not a valid form name or id: '+f);
            }
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
					return el.options[el.options.selectedIndex].value;
                },
                'radio': function(el) { return el.checked ? el.value : null; },
                'checkbox': function(el) { return el.checked ? el.value : null; }
            }
            var params = {};
            var eList = form.getElementsByTagName('*');
            for(var i=0;i<eList.length;i++) {
                n = eList[i];
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
            return this.postify(params);
        },
        postify: function(obj) {
            var params = [];
            for(k in obj) {
                if(obj[k] instanceof Array) {
                    var multivalue = [];
                    for(kk in obj[k]) {
                        if(obj[k][kk] != null) params.push(encodeURIComponent(k.toString())+'='+encodeURIComponent(obj[k][kk].toString()));
                    }
                } else {
                    if(obj[k] != null) params.push(encodeURIComponent(k.toString())+'='+encodeURIComponent(obj[k].toString()));
                }
            }
            return params.join('&');
        }
    }
}();
