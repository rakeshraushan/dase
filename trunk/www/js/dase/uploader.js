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

	/*
	 $("#dropbox, #multiple").html5Uploader({ 
		 name: "uploaded_file", 
		 postUrl: "manage/"+coll,
		 onClientLoadStart: Dase.makeFileList,
	 onServerProgress: function() {Dase.showTime()},
	 onSuccess: Dase.refresh
 }); 
 */

//fileTemplate += "<div class=\"progressbar\"></div>";
var fileTemplate = "<div id=\"{{id}}\">";
fileTemplate += "<div class=\"filename\">{{filename}}</div>";
fileTemplate += "<div class=\"preview\"></div>";
fileTemplate += "</div>";

function slugify(text) {
	text = text.replace(/[^-a-zA-Z0-9,&\s]+/ig, '');
	text = text.replace(/-/gi, "_");
	text = text.replace(/\s/gi, "-");
	return text;
}
$("#multiple").html5Uploader({
	name: "uploaded_file", 
	postUrl: "manage/"+coll,
	onClientLoadStart: function (e, file) {
		var upload = $("#upload");
		if (upload.is(":hidden")) {
			upload.show();
		}
		upload.append(fileTemplate.replace(/{{id}}/g, slugify(file.name)).replace(/{{filename}}/g, file.name));
	},
	onSuccess: function (e, file) {
		$("#" + slugify(file.name)).find(".preview").html("uploaded!");
	},
	onClientLoad: function (e, file) {
		$("#" + slugify(file.name)).find(".preview").append("<img src=\""+Dase.base_href+"www/images/indicator.gif\" alt=\"\">");
	},
	onServerLoadStart: function (e, file) {
		$("#" + slugify(file.name)).find(".progressbar").progressbar({
			value: 0
		});
	},
	onServerProgress: function (e, file) {
		if (e.lengthComputable) {
			var percentComplete = (e.loaded / e.total) * 100;
			$("#" + slugify(file.name)).find(".progressbar").progressbar({
				value: percentComplete
			});
		}
	},
	onServerLoad: function (e, file) {
		$("#" + slugify(file.name)).find(".progressbar").progressbar({
			value: 100
		});
	}
});
};

/*
 Dase.showTime = function() {
	 var msg = document.getElementById("uploadMsg");
	 msg.innerHTML = msg.innerHTML+'.';
 }

 Dase.refresh = function(e) {
	 window.location.reload(true);
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
 */
