Dase.pageInit = function() {
	form = Dase.$('sortForm');
	if (!form) return;
	inputs = document.getElementsByTagName('input');
	for (var i=0;i<inputs.length;i++) {
		inputs[i].onchange = function() {
			_class = this.className;
			_value = this.value;
			if (_class != _value) {
				Dase.$('row'+_class).className = 'shade';
				this.name = 'set_'+this.name;
			} else {
				Dase.$('row'+_class).className = '';
			}
		};
	}
	toppers = document.getElementsByTagName('a');
	for (var i=0;i<toppers.length;i++) {
		if ('topper' == toppers[i].className) { 
			toppers[i].onclick = function() {
				var inp = document.getElementsByName(this.id)[0];
				inp.value = 1;
				inp.name = 'set_'+inp.name;
				form.submit();
				return false;
			};
		}
	}
};

