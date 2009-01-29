Dase = {};

/* from DOM Scripting p. 103 */
Dase.addLoadEvent = function(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		};
	}
};

Dase.$ = function(id) {
	return document.getElementById(id);
};


Dase.addLoadEvent(function() {
	Dase.initForm();
});

Dase.initForm = function() {
	var form =  Dase.$('submitForm');
	form.onsubmit = function() {
		if (!confirm('Are you sure you are ready to submit proposal?')) {
			return false;
		}
	}
}
