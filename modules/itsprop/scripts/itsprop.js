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

Dase.initPersonProposals = function() {
	var links = document.getElementsByTagName('link');
	for (var i=0;i<links.length;i++) {
		if ('proposals' == links[i].rel) {
			var url = links[i].href;
		}
	}
	Dase.getJSON(url,function(resp) {
		var target = Dase.$('userProposals');
		var data = { 'proposals':resp};
		var templateObj = TrimPath.parseDOMTemplate("user_proposals_jst");
		target.innerHTML = templateObj.process(data);
		Dase.removeClass(target,'hide');

	},null,null,'pkeane','okthen');
};

Dase.initDeleteProposal = function() {
	var form = Dase.$('delete_proposal');
	if (!form) return;
	form.onsubmit = function() {
		if (confirm('are you sure?')) {
			Dase.$('content').innerHTML = "deleting proposal";
			Dase.ajax(this.action,'delete',function(resp) {
				Dase.$('content').innerHTML = resp; 
				Dase.initPersonProposals();
			},null,'pkeane','okthen');
		}
		return false;
	}
};

Dase.initProposalShortForm = function() {
	var form = Dase.$('proposalShortForm');
	if (!form) return;
	form.onsubmit = function() {
		//var url = this.options[this.selectedIndex].value+'.json';
		if (!this.proposal_project_type.value) {
			alert('please select a Project Type');
			return false;
		}
		if (!this.proposal_name.value) {
			alert('please enter a Proposal Title');
			return false;
		}
	}
};

Dase.initAuth = function() {
	var metas = document.getElementsByTagName('meta');
	for (var i=0;i<metas.length;i++) {
		if ('special' == metas[i].name) {
			alert(metas[i].content);
		}
	}
}


Dase.addLoadEvent(function() {
	Dase.initAuth();
	Dase.initProposalShortForm();
	Dase.initProposalSections();
	Dase.initPersonProposals();
	Dase.initDeleteProposal();
});

