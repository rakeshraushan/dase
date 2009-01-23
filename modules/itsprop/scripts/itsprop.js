Dase.insertDate = function() {
	var m_names = new Array("Jan", "Feb", "Mar", 
			"Apr", "May", "June", "July", "Aug", "Sept", 
			"Oct", "Nov", "Dec");
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth();
	var curr_year = d.getFullYear();
	var formatted_date = curr_date + "-" + m_names[curr_month] + "-" + curr_year;
	Dase.$('date').innerHTML = formatted_date;;
};


Dase.initProposalSections = function() {
	var form = Dase.$('proposalForm');
	if (!form) return;
	var labels = form.getElementsByTagName('label');
	for (var i=0;i<labels.length;i++) {
		labels[i].onclick = function() {
			var sec = Dase.$(this.getAttribute('for'));
			if (sec) {
				Dase.toggle(sec);
			}
		}
	}
};


Dase.initProposalUpdate = function() {
	var pform = Dase.$('proposalForm');
	if (!pform) return;
	var form_action = pform.action;
	var inputs = document.getElementsByTagName('input');
	for (var i=0;i<inputs.length;i++) {
		var inp = inputs[i];
		if ('update' == inp.className) {
			inp.onclick = function() {
				var id = this.id;
				var section_id = id.substr(7)+'Sec';
				var textarea = Dase.$(section_id).getElementsByTagName('textarea')[0];
				Dase.tmpMsg(this.parentNode,'saving text...');
				var name = textarea.name;
				var data = textarea.value;
				var url = form_action+'/c/'+name;
				Dase.ajax(url,'post',function(txt) {
						var lines = txt.match(/\n/g);
						if (lines) {
						textarea.rows = lines.length;
						} else {
						textarea.rows = 0;
						}
						//Dase.highlight(textarea,800);
						textarea.value = txt;
						},data);
				return false;
			}
		}
	}
};


Dase.addLoadEvent(function() {
	Dase.initProposalSections();
});

