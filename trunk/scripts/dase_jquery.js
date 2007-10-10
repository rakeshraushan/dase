if (!Dase) { var Dase = {}; }
jQuery(function(){ 
		Dase.getUserTags();
		Dase.listMenu();
		Dase.multicheck("checkedCollection");
		Dase.getItemTallies();
		Dase.initBrowse();
		Dase.initDynamicSearchForm();
		}); 

Dase.listMenu = function() { 
	$("ul#menu").find("li > ul").hide().end().find("li:has(ul)").find("a:eq(0)").click(function() {
			$(this).next().toggle();
			return false;
			});
}

Dase.multicheck = function(c) { 
	var coll_list = document.getElementById('collectionList');
	var multi = document.createElement('a');
	multi.setAttribute('href','');
	multi.setAttribute('class','uncheck');
	multi.appendChild(document.createTextNode('check/uncheck all'));
	coll_list.appendChild(multi);
	var boxes = coll_list.getElementsByTagName('input');

	multi.onclick = function() {
		for (var i=0; i<boxes.length; i++) {
			box = boxes[i];
			if ('uncheck' == this.className) {
				box.removeAttribute('checked');
				box.nextSibling.nextSibling.className = '';
			} else {
				box.setAttribute('checked',true);
				box.nextSibling.nextSibling.className = c;
			}
		}	   
		if ('uncheck' == this.className) {
			this.className = 'check';
		} else {
			this.className = 'uncheck';
		}
		return false;
	}

	for (var i=0; i<boxes.length; i++) {
		boxes[i].onclick = function() {
			var link = this.nextSibling.nextSibling;
			if (c == link.className) {
				link.className = '';
			} else {
				link.className = c;
			}
		}
	}	   
}

Dase.getItemTallies = function() {
	if ($("#collectionList").size()) {
		$.get("ajax/item_tallies",function(data){
				$("collection",data).each(function() {
					$('#tally-'+$(this).attr("id")).text("(" + $(this).attr('item_tally') + ")");
					});
				});
	}
}

Dase.getUserTags = function() {
	var eid = $("#userData").text();
	if (eid) {
		$.getJSON("json/" + eid + "/tags",function(json){
				var tags={};
				tags['tagsSelect'] = document.getElementById('tagsSelect');
				var jsonEid = json[eid];
				for (var type in jsonEid) {
				var jsonType = jsonEid[type];
				for (var ascii in jsonType) {
				var jsonAscii = jsonType[ascii];
				tags['tagsSelect'].innerHTML = tags['tagsSelect'].innerHTML + "<input type='checkbox' name='" + ascii + "'> " + jsonAscii + "</input><br>\n";
				tags[type] = tags[type] ? tags[type] : document.getElementById(type);	
				if (tags[type]) {
				tags[type].innerHTML = tags[type].innerHTML + "<li><a href='" + eid + "/tag/" + ascii + "'>" + jsonAscii + "</a></li>\n";
				} } } 
				});
	}
}

Dase.initBrowse = function() {
	if($("#browseColumns").size()) {
		Dase.getAttributes();
		/*
		   $("#catColumn").find("a").each(function() {
		   var category_id = $(this).attr("id").split('_').pop(); //creates closure for click event
		   $(this).click(function() {
		   $("#attColumn").removeClass();
		   $('#valColumn').html(" ").attr("class","empty");
		   if ('admin' == category_id) {
		   Dase.getAdminAttributes(coll);
		   } else {
		   Dase.getAttributes(category_id,coll,public_only);
		   }
		   return false;
		   });
		   });
		 */
	}
}

Dase.getAttributes = function() {
	/*
	   Dase.getHtml('attColumn');
	 */
	$("#attColumn").html("<div class='loading'>Loading Attributes...</div>");
	var url = $("#attColumn").attr("class");
	$.get(url,function(data) {
			$("#attColumn").html(data);
			Dase.bindGetValues(coll);
			});
	if ($("#attColumn").attr("class")) {
		//meaning there is an attr_id embedded in the className of attColumn
		$("#valColumn").removeClass();
	} else {
		$('#valColumn').html(" ").attr("class","empty");
	}
	$('#autocomplete').html(" ");
}

Dase.getAdminAttributes = function(coll) {
	var params = {
token: new Date().getTime(),
	   link_class: "att_link",
	   coll: coll
	};	   
	$("#catColumn < a[class=spill]").attr("class","catLink");
	$("#catLink_admin").attr("class","spill");
	$("#attColumn").html("<div class='loading'>Loading Admin Attributes...</div>");
	$.get("ajax/admin_attributes",params,function(data) {
			$("#attColumn").html(data);
			Dase.bindGetValues(coll);
			});
	if ($("#attColumn").attr("class")) {
		//meaning there is an attr_id embedded in the className of attColumn
		$("#valColumn").removeClass();
	} else {
		$('#valColumn').html(" ").attr("class","empty");
	}
	$('#autocomplete').html(" ");
}

Dase.bindGetValues = function(coll) {
	$("#attColumn").find('a').each(function() {
			var attribute_id = $(this).attr("class").split(" ").pop(); //creates closure for click event
			$(this).click(function() {
				var params = {
token: new Date().getTime(),
attribute_id: attribute_id,
coll: coll
};	   
$("#attColumn//a[class=spill]").attr("class","att_link");
$("#att_link_"+attribute_id).attr("class","spill");
$("#valColumn").html("<div class='loading'>Loading Values...</div>");
$.get("ajax/values_by_attribute",params,function(data) {
	$("#valColumn").html(data).removeClass();
	});
$('#autocomplete').html(" ");
window.scroll(0,0);
return false;
});
			});
Dase.getAttributeTallies(coll);
}

Dase.getAttributeTallies = function(coll) {
	if ($("#getTallies").size()) {
		var params = {
token: new Date().getTime(),
	   coll: coll
		};	   
		if ('adminAtts' == $('#getTallies').attr("class")) { 
			params.admin = 1;
		}
		if ($('#currently_using_cb').size()) {
			params.cb=1;
		}
		$.get("ajax/attribute_tallies",params,function(data) {
				$("attribute",data).each(function() {
					if (0 == $(this).attr('val_tally')) {
					//this makes attributes that have 0 values disappear!!!!
					$('#att_link_'+$(this).attr("id")).hide();
					} else {
					$('#tally-'+$(this).attr("id")).text("(" + $(this).attr('val_tally') + ")");
					}
					});

				});
	}
}

Dase.initDynamicSearchForm = function() {
	$("select.dynamic").change(function() {
			$(this).parent().find("input[type=text]").attr("name",$("option:selected",this).attr("value"));
			});
}

Dase.gid = function(id) {
	return document.getElementById(id);
}

/*
   Dase.createXMLHttpRequest = function() {
   var xmlhttp;
   if (window.XMLHttpRequest) {
   xmlhttp = new XMLHttpRequest();
   } else if (window.ActiveXObject) {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
   } else {
   alert('Perhaps your browser does not support xmlhttprequests?');
   }
   return xmlhttp;
   }

   Dase.getHtml = function(elem_id,my_func) {
   var target = document.getElementById(elem_id);
   if (target) {
   target.innerHTML = '<div class="loading">Loading...</div>';
   var url = target.className;
   }
   var xmlhttp = Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
   xmlhttp.open('GET', url, true);
   xmlhttp.send(null);
   xmlhttp.onreadystatechange = function() {
   if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
   var returnStr = xmlhttp.responseText;
   document.getElementById(elem_id).innerHTML = returnStr;
   if (my_func) {
   my_func();
   }
   } else {
// wait for the call to complete
}
}
}

Dase.getJSON = function(url,my_func) {
var xmlhttp = Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
xmlhttp.open('GET', url, true);
xmlhttp.send(null);
xmlhttp.onreadystatechange = function() {
if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
var json = eval('(' + xmlhttp.responseText + ')');
if (my_func) {
my_func(json);
} else {
return json;

}
} else {
// wait for the call to complete
}
}
}
 */
