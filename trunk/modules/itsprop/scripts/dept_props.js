Dase.itsprop = {};
Dase.itsprop.sort = {};


Dase.initDeptProps = function(highlight_array) {
	var url = Dase.getLinkByRel('dept_props');
	Dase.ajax(url,'get',function(resp) {
		Dase.$('propsList').innerHTML = resp;
		Dase.initVisionToggle();
		Dase.initSort();
		Dase.initToggle();
		if (highlight_array) {
			for (var i=0;i<highlight_array.length;i++) {
				var sorted = highlight_array[i];
				Dase.highlight(Dase.$(sorted),1000,'shade');
			}
		}
	},null,'itsprop',Dase.itsprop.service_pass);
};

Dase.initVisionToggle = function() {
	Dase.$('toggle_vision').onclick = function() {
		Dase.toggle(Dase.$('vision_form'));
		Dase.toggle(Dase.$('vision_statement'));
		return false;
	}
}

Dase.initVisionStatement = function() {
	var form = Dase.$('vision_form');
	if (!form) return;
	form.onsubmit = function() {
		var cont = this.vision.value;
		Dase.ajax(this.action,'put',function(resp) { 
			Dase.pageReload();
		},cont,'itsprop',Dase.itsprop.service_pass);
		return false;
	}
};

Dase.findPlace = function(target_obj,value,proposed_key) {
	if (!target_obj[proposed_key]) {
		target_obj[proposed_key] = value;
		return target_obj;
	} else {
		new_key = parseInt(proposed_key)+1;
		return Dase.findPlace(target_obj,value,new_key);
	}	
};

Dase.doSort = function(json) {

	Dase.itsprop.sorted = [];
	Dase.removeClass(Dase.$('updating_one'),'hide');
	Dase.removeClass(Dase.$('updating_two'),'hide');
	Dase.addClass(Dase.$('button1'),'hide');
	Dase.addClass(Dase.$('button2'),'hide');
	var new_set = {};
	var old_set = {};
	for (var n in json.items) {
		if (json.items[n].is) {
			var id = n.split('/').pop();
			Dase.itsprop.sorted.push('row'+id);
			Dase.findPlace(new_set,n,json.items[n].is);
		} else {
			var id = n.split('/').pop();
			Dase.addClass(Dase.$('row'+id),'fade');
			Dase.findPlace(old_set,n,json.items[n].was);
		}
	}

	var num = 0;
	for (k in old_set) {
		num++;
		Dase.findPlace(new_set,old_set[k],num);
	}

	var keys = new Array();
	for(k in new_set) {
		keys.push(k);
	}

	keys.sort( function(a, b){
			return a-b;
		});

	Dase.itsprop.sort.total = keys.length;

	var final_set = {};
	for (var i = 0; i < keys.length; i++) {
		var id_value = new_set[keys[i]];
		final_set[i+1] = json.items[id_value].url;
	}

	for (var n in final_set) {
		Dase.ajax(final_set[n],'put',function(txt) {
			Dase.itsprop.sort.total--;
			if (!Dase.itsprop.sort.total) {
				Dase.initDeptProps(Dase.itsprop.sorted);
			}
		},n,'itsprop',Dase.itsprop.service_pass);
	}

};

Dase.initSort = function() {
	var table = Dase.$('sorter');
	Dase.itsprop.sort.token = table.className;
	Dase.itsprop.sort.items = {};
	inputs = table.getElementsByTagName('input');
	for (var i=0;i<inputs.length;i++) {
		if ('button1' == inputs[i].id || 'button2' == inputs[i].id) {
			inputs[i].onclick = function() {
				Dase.highlight(table,9000);
				Dase.doSort(Dase.itsprop.sort);
				return false;
			};
		}
		if (inputs[i].id && inputs[i].id != 'button1' && inputs[i].id != 'button2') {
			var inp = inputs[i];
			Dase.itsprop.sort.items[inp.id] = {};
			Dase.itsprop.sort.items[inp.id]['was'] = inp.className;
			Dase.itsprop.sort.items[inp.id]['url'] = inp.getAttribute('action');
			inputs[i].onchange = function() {
				Dase.itsprop.sort.items[this.id]['is'] = this.value;
				_class = this.className;
				_value = this.value;
				_id = this.id;
				if (_class != _value) {
					Dase.$('row'+_id).className = 'shade';
				} else {
					Dase.$('row'+_class).className = '';
				}
			};
		}
	}
	toppers = table.getElementsByTagName('a');
	for (var i=0;i<toppers.length;i++) {
		if ('topper' == toppers[i].className) { 
			toppers[i].onclick = function() {
				var ident = this.id;
				var row_id = ident.replace('topper','row');
				var value_id = row_id.replace('row','');
				Dase.itsprop.sort.items[value_id]['url'] = this.href;
				Dase.itsprop.sort.items[value_id]['is'] = 1;
				Dase.$(row_id).className = 'shade';
				Dase.doSort(Dase.itsprop.sort);
				Dase.highlight(table,9000);
				return false;
			};
		}
	}
};

Dase.addLoadEvent(function() {
	Dase.initDeptProps();
	Dase.initVisionStatement();
});

