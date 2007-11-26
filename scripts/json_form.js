/*
json_form.js -- JSON Form Submission (version 0.5)

Copyright (c) 2006 Mark Nottingham <mnot@pobox.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

--------------------------------------------------------------------------------

See http://www.mnot.net/javascript/json_form.html for documentation.

Requires json.js to be previously loaded. See: http://www.json.org/json.js

--------------------------------------------------------------------------------
*/

var json_form = {
    submit: function (theform) {
        var result = new Object();
        for (i=0; i<theform.elements.length; i++){
            var item = theform.elements[i];
            if (item.type == 'checkbox') {
                if (! result[item.name]) {
                    result[item.name] = new Array();
                }
                if (item.checked) {
                    result[item.name].push(item.value);
                }
            } else {
                result[item.name] = item.value;
            }
        }
        var method = theform.method;
        var uri = theform.action;
        var req = false;
        if(window.XMLHttpRequest) {
            try {
                req = new XMLHttpRequest();
            } catch(e) {
                req = false;
            }
        } else if(window.ActiveXObject) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch(e) {
                req = false;
            }
        }
        if (! req) {
            alert("Your browser does not support XMLHttpRequest.");
        }
        try {
            req.open(method, uri, false);
            req.setRequestHeader("Content-Type", "application/json");
            req.send(result.toJSONString());
        } catch (e) {
            alert("Error: " + e);
        }
        content_location = req.getResponseHeader("Content-Location");
        if (content_location) {
            document.location = content_location;
        } else {
            document.body.innerHTML = req.responseText;
        }
    },

    find: function () {
        var theforms = document.forms;
        for (var i=0; i < theforms.length; i++) {
            theform = theforms[i];
            if (theform.getAttribute('enctype') == "application/json") {
                var old_onsubmit = theform.onsubmit;
                theform.onsubmit = function() {
					old_onsubmit();
					json_form.submit(theform); 
					return false;
				};
            }
        }
    },

    addLoadEvent: function (func) {
        var old_onload = window.onload;
        if (typeof window.onload != 'function') {
            window.onload = func;
        } else {
            window.onload = function() {
                old_onload();
                func();
            };
        }
    }
};

json_form.addLoadEvent(function(){json_form.find()});
