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

Dase.safeCheckHtml = function(text) {
	var form = Dase.$('safeCheckHtml');
	var url = 'json/content';
	Dase.ajax(url,'POST',function(resp) {
			var jsonObj = JSON.parse(resp);
			var json = jsonObj.json;
			var target = Dase.$('validationMsg');
			target.innerHTML = '';
			Dase.createHtmlSet(target,json,'li');
			},text);
};

Dase.initSafeCheckHtml = function() {
	var form = Dase.$('safeCheckHtml');
	if (!form) return;
	form.getElementsByTagName('button')[0].onclick = function() {
		var text = Dase.$('vtext').value;
		Dase.safeCheckHtml(text);
		return false;
	}
	Dase.$('updateButton').onclick = function() {
		var text = Dase.$('vtext').value;
		var url = 'json/content';
		Dase.ajax(url,'POST',function(resp) {
				var jsonObj = JSON.parse(resp);
				var json = jsonObj.json;
				if ('OK' == json[0]) {
				form.submit();
				}
				var target = Dase.$('validationMsg');
				target.innerHTML = '';
				Dase.createHtmlSet(target,json,'li');
				},text);
		return false;
	}
};


Dase.initEditDept = function() {
	var list = Dase.$('listing');
	if (!list) return;
	var links = list.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		if ('edit' == links[i].className) {
			links[i].onclick = function(){
				var thisrow = this.parentNode.parentNode;
				var form_id = thisrow.id.replace(/_display/,'_edit_form');
				var nextrow = Dase.$(form_id);
				Dase.toggle(nextrow);
				return false;
			};
		}
	}
};

Dase.initDeleteProposal = function() {
	var button = Dase.$('proposalDelete');
	if (!button) return;
	button.onclick = function() {
		if (confirm('Are you sure you want to delete "'+button.name+'?"')) {
			var eid = Dase.$('userEid').innerHTML;
			var id = button.value;
			Dase.ajax("u/"+eid+"/proposal/"+id,'delete',function(msg) {
					document.location = document.getElementsByTagName('base')[0].href+"u/"+eid+"/home?msg="+msg;
					});
		}
		return false;
	}
};

Dase.initProposalSections = function() {
	var labels = document.getElementsByTagName('label');
	for (var i=0;i<labels.length;i++) {
		if (!Dase.hasClass(labels[i],'info')) {
			Dase.addClass(labels[i],'hoverable');
			labels[i].onclick = function() {
				var id = this.id;
				var sec = Dase.$(id+'Sec');
				if (sec) {
					var textareas = sec.getElementsByTagName('textarea');
					if (textareas.length > 0) {
						var t = textareas[0];
						var lines = t.value.match(/\n/g);
						if (lines) {
						t.rows = lines.length;
						} 
					}
					Dase.toggle(sec);
				}
			}
		}
	}
};

Dase.initProposalValidation = function() {
	var form = Dase.$('proposalForm');
	if (!form) return;
	form.onsubmit = function() {
		var msg = '';
		if ("" == form.title.value) {
			msg += "Title is required\n";
		}
		if ("" == form.dept_id.options[form.dept_id.selectedIndex].value) {
			msg += "Department is required\n";
		}
		if ("" == form.project_type_id.options[form.project_type_id.selectedIndex].value) {
			msg += "Project Type is required\n";
		}
		if (msg) {
			alert(msg);
			return false;
			msg = '';
		}
		return true;
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

Dase.initAddBudgetItem = function() {
	var pnode = Dase.$('proposal_id');
	if (!pnode) return;
	var proposal_id = pnode.innerHTML;
	var eid = Dase.$('userEid').innerHTML;
	var pform = Dase.$('proposalForm');
	if (!pform) return;
	var form_action = pform.action;
	var button = Dase.$('add_budget_item');
	var section = Dase.$('itemsSec');
	if (!section) return;
	button.onclick = function() {
		var inputs = section.getElementsByTagName('input');
		var data = {}; 
		var error = '';
		for (var i=0;i<inputs.length;i++) {
			var inp = inputs[i];
			data[inp.name]=inp.value;
			if (''==inp.value) {
				var label = inp.name.replace(/_/,' ');
				error += label+" is required\n";
			}
		}
		var desc = Dase.$('budget_item_description');
		data['budget_item_description']=desc.value;
		var type = Dase.$('budget_type_id');
		var type_val = type.options[type.selectedIndex].value;
		data[type.name]=type.value;
		if (!type_val) {
				error += "please select a budget item type\n";
		}
		if (error) {
			alert(error);
			return false;
		}
		//no error, so OK to clearform
		for (var i=0;i<inputs.length;i++) {
			if ('text' == inputs[i].type) {
				inputs[i].value = ''; //reset text boxes
			}
		}
		type.selectedIndex = 0;
		desc.value = ''; //reset textarea
		HTTP.post(Dase.base_href + "u/"+eid+"/proposal/"+proposal_id+"/budget",data,Dase.loadProposalBudgetData);
		return false;
	}
};

Dase.initSetRadio = function() {
	var form = Dase.$('proposalForm');
	if (!form) return;
	var radios = form.getElementsByTagName('input');
	for (var i=0;i<radios.length;i++) {
		var radio = radios[i];
		//if ('radio' == radio.type && !radio.checked) {
		if ('radio' == radio.type) {
			if (radio.checked) {
				Dase.addClass(Dase.$(radio.name+radio.value),'highlighted');
			} else {
				Dase.removeClass(Dase.$(radio.name+radio.value),'highlighted');
			}
			radio.onclick = function() {
				var url = form.action+'/c/'+this.name;
				Dase.ajax(url,'POST',function(txt) {
						Dase.highlight(Dase.$('assistantSec'),600);
						},this.value);
				Dase.initSetRadio();
			}
		}
	}
}

Dase.initAddClass = function() {
	var pnode = Dase.$('proposal_id');
	if (!pnode) return;
	var proposal_id = pnode.innerHTML;
	var eid = Dase.$('userEid').innerHTML;
	var pform = Dase.$('proposalForm');
	if (!pform) return;
	var form_action = pform.action;
	var button = Dase.$('add_class');
	var section = Dase.$('classesSec');
	if (!section) return;
	button.onclick = function() {
		var inputs = section.getElementsByTagName('input');
		var data = {}; 
		var error = '';
		for (var i=0;i<inputs.length;i++) {
			var inp = inputs[i];
			data[inp.name]=inp.value;
			if (''==inp.value) {
				var label = inp.name.replace(/_/,' ');
				error += label+" is required\n";
			}
		}
		var freq = Dase.$('course_frequency');
		var freq_val = freq.options[freq.selectedIndex].value;
		data[freq.name]=freq.value;
		if (!freq_val) {
				error += "please select a course frequency\n";
		}
		freq.selectedIndex = 0;
		if (error) {
			alert(error);
			return false;
		}
		//no error, so OK to clearform
		for (var i=0;i<inputs.length;i++) {
			if ('text' == inputs[i].type) {
				inputs[i].value = ''; //reset text boxes
			}
		}
		freq.selectedIndex = 0;
		HTTP.post(Dase.base_href + "u/"+eid+"/proposal/"+proposal_id+"/classes",data,Dase.loadProposalClassesData);
		return false;
	}
};

Dase.loadProposalClassesData = function() {
	if (Dase.$('pageHook').innerHTML != 'proposal_getForm') return;
	var proposal_id = Dase.$('proposal_id').innerHTML;
	if (!proposal_id) return;
	Dase.loadingMsg(true);
	var eid = Dase.$('userEid').innerHTML;
	Dase.removeChildren(Dase.$('classes_list'));
	Dase.getHtml(Dase.base_href + "u/"+eid+"/html/proposal/"+proposal_id+"/classes",'classes_list',Dase.initDeleteCourse);
};

Dase.loadProposalBudgetData = function() {
	if (Dase.$('pageHook').innerHTML != 'proposal_getForm') return;
	var proposal_id = Dase.$('proposal_id').innerHTML;
	if (!proposal_id) return;
	Dase.loadingMsg(true);
	var eid = Dase.$('userEid').innerHTML;
	Dase.removeChildren(Dase.$('budget_list'));
	Dase.getHtml(Dase.base_href + "u/"+eid+"/html/proposal/"+proposal_id+"/budget",'budget_list',Dase.initDeleteBudgetItem);
};

Dase.initDeleteCourse = function() {
	var return_msg;
	var classes_list = Dase.$('classes_list');
	if (!classes_list) return;
	var d_links = classes_list.getElementsByTagName('a');
	for (var i=0;i<d_links.length;i++) {
		link = d_links[i];
		if ('delete' == link.className) {
			link.onclick = function() {
				if (confirm('are you sure?')) {
					Dase.ajax(this.href,'delete',function(return_msg) {
							//Dase.tmpMsg(classes_list,return_msg);
							Dase.loadProposalClassesData();
							});
				}
				return false;
			}
		}
	}
};

Dase.initDeleteBudgetItem = function() {
	var return_msg;
	var budget_list = Dase.$('budget_list');
	if (!budget_list) return;
	var d_links = budget_list.getElementsByTagName('a');
	for (var i=0;i<d_links.length;i++) {
		link = d_links[i];
		if ('delete' == link.className) {
			link.onclick = function() {
				if (confirm('are you sure?')) {
					Dase.ajax(this.href,'delete',function(return_msg) {
							//Dase.tmpMsg(classes_list,return_msg);
							Dase.loadProposalBudgetData();
							});
				}
				return false;
			}
		}
	}
};

Dase.initSubmitProposal = function() {
	var button = Dase.$('proposal_submit');
	var checkbox = Dase.$('email_confirm');
	if (!button) return;
	if (!checkbox) return;
	button.onclick = function() {
		if (true != checkbox.checked) {
			alert("Please confirm that you have sent an email\nto the Departmental Chair");
			return false;
		} 
	}
};

Dase.addLoadEvent(function() {
		Dase.initSafeCheckHtml();
		Dase.insertDate();
		Dase.initEditDept();
		Dase.initDeleteProposal();
		Dase.initProposalValidation();
		Dase.initProposalSections();
		Dase.initProposalUpdate();
		Dase.initAddBudgetItem();
		Dase.initAddClass();
		Dase.initSetRadio();
		Dase.initSubmitProposal();
		Dase.loadProposalClassesData();
		Dase.loadProposalBudgetData();
		});

