Dase.itsprop = {};

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


Dase.initProposalForm = function() {
	var form = Dase.$('proposalForm');
	if (!form) return;
	var labels = form.getElementsByTagName('label');
	for (var i=0;i<labels.length;i++) {
		labels[i].onclick = function() {
			var sec = Dase.$('div_'+this.getAttribute('for'));
			if (sec) {
				Dase.toggle(sec);
			}
		}
	}
	var inputs = document.getElementsByTagName('input');
	for (var i=0;i<inputs.length;i++) {
		var inp = inputs[i];
		if ('update' == inp.value) {
			inp.onclick = function() {
				var clicked = this;
				var url = this.getAttribute('action');
				var textarea = Dase.$(this.className);
				Dase.addClass(textarea,'pending');
				Dase.addClass(clicked,'updating');
				clicked.value = 'updating...';
				var name = textarea.name;
				var data = textarea.value;
				Dase.ajax(url,'put',function(txt) {
					Dase.removeClass(textarea,'pending');
					Dase.removeClass(clicked,'updating');
					clicked.value = 'update';
					var words = txt.split(' ').length;
					if (words) {
						textarea.rows = words/11+2;
					} else {
						textarea.rows = 0;
					}
					Dase.highlight(textarea,800);
					textarea.value = txt;
				},data,'itsprop',Dase.itsprop.service_pass);
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

	},null,null,'itsprop',Dase.itsprop.service_pass);
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
			},null,'itsprop',Dase.itsprop.service_pass);
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

Dase.initProposalCourses = function() {
	var url = Dase.getLinkByRel('courses');
	if (!url) return;
	Dase.getJSON(url,function(resp) {
		var target = Dase.$('classesList');
		var data = { 'classes':resp};
		var templateObj = TrimPath.parseDOMTemplate("proposal_courses_jst");
		target.innerHTML = templateObj.process(data);
		Dase.removeClass(target,'hide');
		var button = Dase.$('add_class');
		button.value = 'add';
		Dase.removeClass(button,'updating');
		var cform = Dase.$('courseForm');
		if (cform) { cform.reset(); }
		var links = target.getElementsByTagName('a');
		for (var i=0;i<links.length;i++) {
			if ('delete' == links[i].className) {
				links[i].onclick = function() {
					if (confirm('are you sure?')) {
						this.className = 'modify';
						this.innerHTML = 'removing...';
						Dase.ajax(this.href,'delete',function(resp) {
							Dase.initProposalCourses();
						},null,'itsprop',Dase.itsprop.service_pass);
					}
					return false;
				}
			}
		}
	},null,null,'itsprop',Dase.itsprop.service_pass);


}

Dase.initCourseForm = function() {
	var cform = Dase.$('courseForm');
	if (!cform) return;
	cform.onsubmit = function() {
		if (!this.course_title.value) {
			alert('please enter a Course Title');
			return false;
		}
		if (!this.course_number.value) {
			alert('please enter a Course Number');
			return false;
		}
		if (!this.course_enrollment.value) {
			alert('please enter enrollment');
			return false;
		}
		if (!this.course_frequency.value) {
			alert('please select a frequency');
			return false;
		}
		var button = Dase.$('add_class');
		button.value = 'adding course info';
		Dase.addClass(button,'updating');
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(this.action,'post',function(resp) { 
			Dase.initProposalCourses();
		},Dase.form.serialize(this),null,null,content_headers); 
		return false;
	}
}

Dase.initModule = function() {
	var url = Dase.getLinkByRel('service_pass');
	Dase.ajax(url,'get',function(resp) {
		if (32 == resp.length) {
			Dase.itsprop.service_pass = resp;
		}
		Dase.initProposalShortForm();
		Dase.initProposalForm();
		Dase.initProposalCourses();
		Dase.initPersonProposals();
		Dase.initDeleteProposal();
		Dase.initCourseForm();
	},null,null,null,null,function(error) {
		alert(error);
	});
};


Dase.addLoadEvent(function() {
	Dase.initModule();
});

