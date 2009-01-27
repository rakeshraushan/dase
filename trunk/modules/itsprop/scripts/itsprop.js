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
	//alert('initProposalForm');
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
	var sta_no = Dase.$('sta_no');
	sta_no.onclick = function() {
		var curr = Dase.$(this.name+'_status');
		curr.innerHTML = 'updating...';
		Dase.ajax(this.getAttribute('action'),'put',function(txt) {
			curr.innerHTML = txt;
		},this.value,'itsprop',Dase.itsprop.service_pass);
	}
	var sta_yes = Dase.$('sta_yes');
	sta_yes.onclick = function() {
		var curr = Dase.$(this.name+'_status');
		curr.innerHTML = 'updating...';
		Dase.ajax(this.getAttribute('action'),'put',function(txt) {
			curr.innerHTML = txt;
		},this.value,'itsprop',Dase.itsprop.service_pass);
	}
	var workshop_no = Dase.$('workshop_no');
	workshop_no.onclick = function() {
		var curr = Dase.$(this.name+'_status');
		curr.innerHTML = 'updating...';
		Dase.ajax(this.getAttribute('action'),'put',function(txt) {
			curr.innerHTML = txt;
		},this.value,'itsprop',Dase.itsprop.service_pass);
	}
	var workshop_yes = Dase.$('workshop_yes');
	workshop_yes.onclick = function() {
		var curr = Dase.$(this.name+'_status');
		curr.innerHTML = 'updating...';
		Dase.ajax(this.getAttribute('action'),'put',function(txt) {
			curr.innerHTML = txt;
		},this.value,'itsprop',Dase.itsprop.service_pass);
	}
};

Dase.initPersonProposals = function() {
	var links = document.getElementsByTagName('link');
	for (var i=0;i<links.length;i++) {
		if ('proposals' == links[i].rel) {
			var url = links[i].href;
		}
	}
	if (!url) return;
	//alert('initPersonProposals');
	Dase.getJSON(url,function(resp) {
		var target = Dase.$('userProposals');
		var data = { 'proposals':resp};
		var templateObj = TrimPath.parseDOMTemplate("user_proposals_jst");
		target.innerHTML = templateObj.process(data);
		Dase.removeClass(target,'hide');

	},null,null,'itsprop',Dase.itsprop.service_pass);
};

Dase.initDeleteProposal = function() {
	var form = Dase.$('deleteProposal');
	if (!form) return;
	//alert('initDeleteProposal');
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
	//alert('initProposalShortForm');
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
	//alert('initProposalCourses');
	Dase.getJSON(url,function(resp) {
		var target = Dase.$('classesList');
		var data = { 'classes':resp};
		var templateObj = TrimPath.parseDOMTemplate("proposal_courses_jst");
		target.innerHTML = templateObj.process(data);
		Dase.removeClass(target,'hide');
		var button = Dase.$('add_class');
		button.value = 'add';
		Dase.removeClass(button,'updating');
		var form = Dase.$('courseForm');
		if (form) { form.reset(); }
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

Dase.initProposalBudgetItems = function() {
	var url = Dase.getLinkByRel('budget_items');
	if (!url) return;
	//alert('initProposalBudgetItems');
	Dase.getJSON(url,function(resp) {
		var target = Dase.$('budgetItemsList');
		var grand_total = 0;
		for (var it in resp) {
			var p = resp[it].metadata.budget_item_price;
			var q = resp[it].metadata.budget_item_quantity;
			resp[it].metadata.total = p*q;
			grand_total += p*q;
		}
		var data = { 'items':resp};
		data['grand_total'] = grand_total;
		var templateObj = TrimPath.parseDOMTemplate("proposal_budget_items_jst");
		target.innerHTML = templateObj.process(data);
		Dase.removeClass(target,'hide');
		var button = Dase.$('add_budget_item');
		button.value = 'add budget item';
		Dase.removeClass(button,'updating');
		var form = Dase.$('budgetForm');
		if (form) { form.reset(); }
		var links = target.getElementsByTagName('a');
		for (var i=0;i<links.length;i++) {
			if ('delete' == links[i].className) {
				links[i].onclick = function() {
					if (confirm('are you sure?')) {
						this.className = 'modify';
						this.innerHTML = 'removing...';
						Dase.ajax(this.href,'delete',function(resp) {
							Dase.initProposalBudgetItems();
						},null,'itsprop',Dase.itsprop.service_pass);
					}
					return false;
				}
			}
		}
	},null,null,'itsprop',Dase.itsprop.service_pass);
}

Dase.initCourseForm = function() {
	var form = Dase.$('courseForm');
	if (!form) return;
	//alert('initCourseForm');
	form.onsubmit = function() {
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
		if (isNaN(this.course_enrollment.value)) {
			alert('enrollment must be a number');
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

Dase.initBudgetForm = function() {
	var form = Dase.$('budgetForm');
	if (!form) return;
	//alert('initBudgetForm');
	form.onsubmit = function() {
		if (!this.budget_item_type.value) {
			alert('please select a type');
			return false;
		}
		if (!this.budget_item_quantity.value) {
			alert('please enter a quantity');
			return false;
		}
		if (!this.budget_item_price.value) {
			alert('please enter a price');
			return false;
		}
		if (!this.budget_item_description.value) {
			alert('please enter a description');
			return false;
		}
		if (isNaN(this.budget_item_quantity.value)) {
			alert('quantity must be a number');
			return false;
		}
		if (isNaN(this.budget_item_price.value)) {
			alert('price must be a number');
			return false;
		}
		var button = Dase.$('add_budget_item');
		button.value = 'adding budget item...';
		Dase.addClass(button,'updating');
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(this.action,'post',function(resp) { 
			Dase.initProposalBudgetItems();
		},Dase.form.serialize(this),null,null,content_headers); 
		return false;
	}
}

Dase.initModule = function() {
	var url = Dase.getLinkByRel('service_pass');
	if (!url) return;
	//alert('initModule');
	Dase.ajax(url,'get',function(resp) {
		if (32 == resp.length) {
			Dase.itsprop.service_pass = resp;
		}
		Dase.initProposalShortForm();
		Dase.initProposalForm();
		Dase.initProposalCourses();
		Dase.initCourseForm();
		Dase.initProposalBudgetItems();
		Dase.initBudgetForm();
		Dase.initPersonProposals();
		Dase.initDeleteProposal();
	},null,null,null,null,function(error) {
		alert(error);
	});
};


Dase.addLoadEvent(function() {
	Dase.initModule();
});

