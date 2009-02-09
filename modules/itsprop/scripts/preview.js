Dase.itsprop = {};

Dase.htmlEntities = function (str) {
	return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
};

Dase.itsprop.initForm = function() {
	var form =  Dase.$('submitForm');
	if (!form) return;
	var chair_name = form.chair_name.value;
	var chair_email = form.chair_email.value;
	if (!form) return;
	form.onsubmit = function() {
		if (!confirm('Are you sure you are ready to submit proposal?\n(a copy will be sent to '+chair_name+' <'+chair_email+'>)')) {
			return false;
		}
		//email the proposal
		var html = Dase.htmlEntities(Dase.$('container').innerHTML);
		var act = this.getAttribute('action');
		var url = act.replace('archiver','email');
		Dase.ajax(url,'post',function(txt) {
		},html,'itsprop',Dase.itsprop.service_pass);
	}
}

Dase.itsprop.initUnsubmit = function() {
	var button = Dase.$('unsubmitFormButton');
	if (!button) return;
	button.onclick = function() {
		var url = this.getAttribute('action');
		Dase.ajax(url,'post',function(txt) {
			Dase.pageReload('unsubmitted');
		},null,'itsprop',Dase.itsprop.service_pass);
		return false;
	}
};

Dase.addLoadEvent(function() {
	Dase.itsprop.initForm();
	Dase.itsprop.initUnsubmit();
});


/*
$(document).ready(function(){
	String.prototype.htmlEntities = function () {
		return this.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
	};
	$('#submitFormButton').click(function(){
		if (!confirm('Are you sure you are ready to submit proposal?')) {
			return false;
		}
		var html = $('#container').html();
		html = html.htmlEntities();
		var href = $('div.inner p a').attr('href');
		$.post(href+'/email',{container:html},function(data){alert(data);});
	});
});
*/
