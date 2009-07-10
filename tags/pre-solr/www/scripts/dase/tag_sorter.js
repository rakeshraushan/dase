Dase.pageInit = function() {
	//javascript will change the 'name' attribute from
	//sort_item to set_sort_item
	//the handler for the post will only concern itself 
	//with variables named set_sort_item
	form = Dase.$('sortForm');
	if (!form) return;
	inputs = document.getElementsByTagName('input');
	for (var i=0;i<inputs.length;i++) {
		inputs[i].onchange = function() {
			_class = this.className;
			_value = this.value;
			if (_class != _value) {
				Dase.$('row'+_class).className = 'shade';
				this.name = 'set_'+this.name.replace('set_','');
			} else {
				Dase.$('row'+_class).className = '';
			}
		};
	}
	toppers = document.getElementsByTagName('a');
	for (var i=0;i<toppers.length;i++) {
		if ('topper' == toppers[i].className) { 
			toppers[i].onclick = function() {
				var inp = document.getElementById('input_'+this.id);
				if (!inp) return;
				inp.value = 1;
				inp.name = 'set_'+inp.name;
				form.submit();
				return false;
			};
		}
	}
};

