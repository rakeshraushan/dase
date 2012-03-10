Dase.pageInitUser = function(eid) {
	var coll = Dase.$('collectionAsciiId').innerHTML;
	Dase.getJSON(Dase.base_href+'user/'+eid+'/'+coll+'/recent.json',function(recent){
		var h = new Dase.htmlbuilder;
		for (var sernum in recent) {
			var rec = recent[sernum];
			var li = h.add('li');
			var a = li.add('a');
			a.set('href',rec.item_record_href);
			a.add('img',{'src':rec.thumbnail_href});
			var h4 = li.add('h4');
			h4.add('a',{'href':rec.item_record_href},rec.title);
		}
		h.attach(Dase.$('recent'));
	});

	//requires jquery & jquery.html5uploader
	$("#dropbox, #multiple").html5Uploader({ 
		name: "uploaded_file", 
		postUrl: "manage/"+coll,
		onClientLoadStart: Dase.makeFileList
	}); 

};

Dase.makeFileList = function() {
	var msg = document.getElementById("uploadMsg");
	msg.className = '';
	var input = document.getElementById("multiple");
	var ul = document.getElementById("fileList");
	while (ul.hasChildNodes()) {
		ul.removeChild(ul.firstChild);
	}
	for (var i = 0; i < input.files.length; i++) {
		var li = document.createElement("li");
		li.innerHTML = input.files[i].name;
		ul.appendChild(li);
	}
	if(!ul.hasChildNodes()) {
		var li = document.createElement("li");
		li.innerHTML = 'No Files Selected';
		ul.appendChild(li);
	}
};
