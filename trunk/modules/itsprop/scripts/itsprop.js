Dase.itsprop = {};

Dase.itsprop.updateMsg = function(displayBool) {
	var upd = Dase.$('updateMsg');
	if (!upd) return;
	if (displayBool) {
		Dase.removeClass(upd,'hide');
		setTimeout('Dase.itsprop.updateMsg(false)',1000);
	} else {
		Dase.addClass(upd,'hide');
	}
}

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

Dase.initEditTitle = function() {
	var edit = Dase.$('editTitleToggle');
	if (!edit) return;
	edit.onclick = function() {
		Dase.addClass(Dase.$('prop_title'),'hide');
		Dase.removeClass(Dase.$('editTitleForm'),'hide');
		return false;
	}
	var submit = Dase.$('editTitleSubmit');
	var val = Dase.$('editTitleValue');
	submit.onclick = function() {
		var put_url = this.getAttribute('action');
		var parts = put_url.split('/');
		parts.pop();
		var title_url = parts.join('/')+'/title';
		Dase.ajax(this.getAttribute('action'),'put',function() {
			Dase.$('editTitleForm').innerHTML = 'updating title...';
			Dase.ajax(title_url,'put',function() {
				Dase.$('editTitleForm').innerHTML = 'updating title......';
				Dase.pageReload('updated title');
			},val.value,'itsprop',Dase.itsprop.service_pass);
		},val.value,'itsprop',Dase.itsprop.service_pass);
	}
}

Dase.initProposalForm = function() {
	var form = Dase.$('proposalForm');
	if (!form) return;
	//alert('initProposalForm');
	var labels = form.getElementsByTagName('label');
	for (var i=0;i<labels.length;i++) {
		labels[i].onclick = function() {
			var sec = Dase.$('div_'+this.getAttribute('for'));
			if (sec) {
				var span = this.getElementsByTagName('span')[0];
				if ('expand [+]' == span.innerHTML) {
					span.innerHTML = 'collapse [-]';
				} else {
					span.innerHTML = 'expand [+]';
				}
				Dase.toggle(sec);
			}
		}
	}
	var inputs = document.getElementsByTagName('input');
	for (var i=0;i<inputs.length;i++) {
		var inp = inputs[i];
		var cname = inp.className;
		var tarea = Dase.$(cname);
		if (tarea) {
			tarea.onchange = function() {
				var unsaved = Dase.$('unsaved');
				Dase.removeClass(unsaved,'hide');
				plink = Dase.$('previewLink');
				plink.onclick = function() {
					alert('you have unsaved changes');
					return false;
				}
			}
		}
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
					var unsaved = Dase.$('unsaved');
					Dase.addClass(unsaved,'hide');
					plink = Dase.$('previewLink');
					plink.onclick = function() {
						return true;
					}
					Dase.removeClass(textarea,'pending');
					Dase.removeClass(clicked,'updating');
					clicked.value = 'update';
					var words = txt.split(' ').length;
					if (words) {
						textarea.rows = words/11+2;
					} else {
						textarea.rows = 0;
					}
					Dase.itsprop.updateMsg(true);
					Dase.highlight(textarea,1000);
					textarea.value = txt;

					/*
					var.words;
					var a = txt.replace(/\s/g,' ');
					parts = a.split(' ');
					for (var i=0;i<parts.length;i++) {
						if (parts[i].length > 1) {
							words++;
						}
					}
					*/

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
	Dase.getJSON(url,function(props) {
		var h = new Dase.htmlbuilder;
		for (var i=0;i<props.length;i++) {
			var prop = props[i];
			var li = h.add('li');
			li.add('a',{'class':'sub','target':'_blank','href':'proposal/'+prop.serial_number},prop.title);
		}
		var target = Dase.$('userProposals');
		h.attach(target);
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
	Dase.getJSON(url,function(classes) {
		var target = Dase.$('classesList');
		var ul = new Dase.htmlbuilder('ul',{'id':'courses'});
		for (var ser in classes) {
			var c = classes[ser];
			var li = ul.add('li');
			li.setText(c.metadata.title+' ('+c.metadata.course_number+') ['+c.metadata.course_enrollment+' students '+c.metadata.course_frequency+']');
			var a = li.add('a',{'href':c.edit,'class':'delete'},'delete');
		}
		ul.attach(target);
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
	Dase.getJSON(url,function(items) {
		var target = Dase.$('budgetItemsList');
		var grand_total = 0;
		for (var it in items) {
			var p = items[it].metadata.budget_item_price;
			var q = items[it].metadata.budget_item_quantity;
			items[it].metadata.total = p*q;
			grand_total += p*q;
		}
		items['grand_total'] = grand_total;

		var table = new Dase.htmlbuilder('table',{'class':'listing','id':'budget_items_table'});
		var tr = table.add('tr');
		var th = tr.add('th');
		th = tr.add('th',null,'type');
		th = tr.add('th',null,'description');
		th = tr.add('th',null,'quantity');
		th = tr.add('th',null,'price per unit');
		th = tr.add('th',null,'total');
		for (var ser in items) {
			var it = items[ser];
			if (it.metadata) {
				var tr = table.add('tr');
				var td = tr.add('td');
				td.add('a',{'href':it.edit,'class':'delete'},'delete');
				tr.add('td',null,it.metadata.budget_item_type);
				tr.add('td',null,it.metadata.budget_item_description);
				tr.add('td',null,it.metadata.budget_item_quantity);
				tr.add('td',null,'$'+it.metadata.budget_item_price);
				tr.add('td',null,'$'+it.metadata.total);
			}
		}
		var tr = table.add('tr');
		tr.add('td',{'colspan':'5'},'grand total:');
		tr.add('td',null,'$'+items.grand_total);
		table.attach(target);
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
		Dase.initEditTitle();
		Dase.initProposalCourses();
		Dase.initCourseForm();
		Dase.initProposalBudgetItems();
		Dase.initBudgetForm();
		Dase.initPersonProposals();
		Dase.initDeleteProposal();
	},null,null,null,null,function(error) {
		//alert(error);
		window.location.href = Dase.base_href+'modules/itsprop/logout';
	});
};


Dase.addLoadEvent(function() {
	Dase.initModule();
});

