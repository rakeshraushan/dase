Dase.itsprop = {};
Dase.itsprop.sort = {};

Dase.initDeptProps = function(highlight_array) {
	var url = Dase.getLinkByRel('dept_props');
	Dase.ajax(url,'get',function(resp) {
		Dase.$('propsList').innerHTML = resp;
		$('#sortable').sortable();
		$('#update_sort').click(function(){
			var count = 1;
			var inner_count = 1;
			var size = $('#sortable li').size();
			$('#sortable li').css('background-color','#FFFFCC');
			$('body').css('cursor','wait');
			$('#sortable li').each(function(){
				if(count <= size){
					Dase.ajax(this.className,'put',function(resp) { 
						if(resp){inner_count++;}
						if(inner_count == size){
							Dase.initDeptProps();
							$('body').css('cursor','default');
						}
					},count,'itsprop',Dase.itsprop.service_pass);
					count += 1;
				}
			});
		return false;
		});
		Dase.initVisionToggle();
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
};

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

Dase.addLoadEvent(function() {
	Dase.initDeptProps();
	Dase.initVisionStatement();
});

