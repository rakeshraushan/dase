Dase.pageInit = function() {
	for (var j=1;j<11;j++) {
		var up_form = Dase.$('uploader_'+j+'_form');
		if (up_form) {
			up_form.reset();
			var iframe = up_form.parentNode.getElementsByTagName('iframe')[0];
			iframe.onload = function() { 
				//debug
//				alert(this.contentDocument.body.innerHTML);
				try {
					var res = JSON.parse(this.contentDocument.body.innerHTML);
				} catch(e) {
					alert(e);
					return;
				}
				if(res.status == 'ok') {
					var target = Dase.$('results_'+res.num); 
					target.innerHTML = '<img src="'+res.thumbnail_url+'"/><br/>'+res.num+'. <a href="'+res.item_url+'">'+res.filename+'</a>';
					Dase.removeClass(target,'hide');
					Dase.addClass(target,'ok');
				} else {
					var target = Dase.$('results_'+res.num); 
					target.innerHTML = '<img src="www/images/tango-icons/dialog-error.png"/> '+res.num+'. '+res.message;
					Dase.removeClass(target,'hide');
					Dase.addClass(target,'error');
				}
				Dase.addClass(Dase.$('queue_'+res.num),'hide');
			}
			up_form.target = iframe.id;
			var inp = 	up_form.getElementsByTagName('input')[0];
			inp.onchange = function() {
				var num = this.parentNode.num.value;
				var inp = this.parentNode.getElementsByTagName('input')[0];
				Dase.addClass(Dase.$('uploader_'+num),'hide');
				Dase.createElem(Dase.$('queue_'+num),' '+inp.value,'span');
				var link = Dase.createElem(Dase.$('queue_'+num),' [x]','a');
				link.onclick = function() {
					Dase.addClass(Dase.$('queue_'+num),'hide');
					//abort iframe load
					var iframe = Dase.$('uploader_'+num+'_target');
					iframe.src = '';
					return false;
				}
				link.className="delete";
				link.href="#";
				Dase.removeClass(Dase.$('queue_'+num),'hide');
				pos = parseInt(num)+1;
				Dase.removeClass(Dase.$('uploader_'+pos),'hide');
				this.parentNode.submit();
			}
		}
	}
};

