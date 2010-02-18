Dase.pageInit = function() {
	var url = Dase.base_href+'collections.json';
	Dase.getJSON(url,function(collections){

		var h = new Dase.htmlbuilder('ul');
		for (var i=0;i<collections.length;i++) {
			var c = collections[i];
			var li = h.add('li');
			var a = li.add('a',{'href':'collection/'+c.ascii_id});
			a.setText(c.collection_name+' ('+c.ascii_id+') '+c.item_count+' items');
			if (c.item_count < 5) {
				li.add('a',{'href':'collection/'+c.ascii_id,'id':c.collection_name,'class':'delete'},'delete');
			}
		}
		h.attach(Dase.$('cList'));
		var links = Dase.$('cList').getElementsByTagName('a');
		for (var i=0;i<links.length;i++) {
			if ('delete' == links[i].className) {
				links[i].onclick = function() {
					if (!confirm('Do you REALLY want to delete \n'+this.id+' ??')) return false;
					Dase.ajax(this.href,'delete',function(resp) {
						alert(resp);
						Dase.pageReload();
					},null,Dase.user.eid,Dase.user.htpasswd);
					return false;
				} 
			} 
		}
	},null);
};
