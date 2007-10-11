if (!Dase) { var Dase = {}; }
jQuery(function(){ 
		Dase.getUserTags();
		Dase.initMenu('menu');
		Dase.multicheck("checkedCollection");
		Dase.getItemTallies();
		Dase.initBrowse();
		Dase.initDynamicSearchForm();
		}); 

Dase.toggle = function(el) {
	if ('hide' == el.className) {
		el.className = 'show';
	} else {
		el.className = 'hide';
	}
}

Dase.initMenu = function(id) { 
	var menu = document.getElementById(id);
	if (menu) {
		var listItems = menu.getElementsByTagName('li');
		for (var i=0;i<listItems.length;i++) {
			var listItem = listItems[i];
			var sub = listItem.getElementsByTagName('ul');
			if (sub) {
				var listItemLink = listItem.getElementsByTagName('a')[0];
				if (listItemLink) {
					listItemLink.onclick = function() {
						Dase.toggle(this.parentNode.getElementsByTagName('ul')[0]);
						return false;
					}
				}
			}
		}
	}
}

Dase.multicheck = function(c) { 
	var coll_list = document.getElementById('collectionList');
	if (!coll_list) { return; }
	var multi = document.createElement('a');
	multi.setAttribute('href','');
	multi.setAttribute('class','uncheck');
	multi.setAttribute('className','uncheck');
	multi.appendChild(document.createTextNode('check/uncheck all'));
	coll_list.appendChild(multi);
	var boxes = coll_list.getElementsByTagName('input');

	multi.onclick = function() {
		for (var i=0; i<boxes.length; i++) {
			box = boxes[i];
			if ('uncheck' == this.className) {
				//box.removeAttribute('checked');
				box.checked = null;
				box.parentNode.getElementsByTagName('a')[0].className = '';
			} else {
				//box.setAttribute('checked',true);
				box.checked = true;
				box.parentNode.getElementsByTagName('a')[0].className = c;
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
			var link = this.parentNode.getElementsByTagName('a')[0];
			if (c == link.className) {
				link.className = '';
			} else {
				link.className = c;
			}
		}
	}	   
}

Dase.getItemTallies = function() {
	if (jQuery("#collectionList").size()) {
		jQuery.get("ajax/item_tallies",function(data){
				jQuery("collection",data).each(function() {
					jQuery('#tally-'+jQuery(this).attr("id")).text("(" + jQuery(this).attr('item_tally') + ")");
					});
				});
	}
}

Dase.getUserTags = function() {
	var eid = jQuery("#userData").text();
	//note: the message was overkill on every request....
	//var msg = document.getElementById('ajaxMenuMsg');
	if (eid) {
		//msg.innerHTML = 'loading data for ' + eid + '...';
		jQuery.getJSON("json/" + eid + "/tags",function(json){
				var tags={};
				tags['tagsSelect'] = document.getElementById('tagsSelect');
				var jsonEid = json[eid];
				for (var type in jsonEid) {
				var jsonType = jsonEid[type];
				for (var ascii in jsonType) {
				var jsonAscii = jsonType[ascii];
				tags['tagsSelect'].innerHTML = tags['tagsSelect'].innerHTML + "<input type='checkbox' name='" + ascii + "'> " + jsonAscii + "</input><br>\n";
				//first time through we grab the element using getElementById
				tags[type] = tags[type] ? tags[type] : document.getElementById(type);	
				if ((tags[type]) && ('cart' != type)) {
				tags[type].innerHTML = tags[type].innerHTML + "<li><a href='" + eid + "/tag/" + ascii + "'>" + jsonAscii + "</a></li>\n";
				} 
				} } 
				});
		//setTimeout(function() { msg.innerHTML = ''}, 2400);	
	}
}

Dase.initBrowse = function() {
	if(jQuery("#browseColumns").size()) {
		Dase.getAttributes();
		/*
		   jQuery("#catColumn").find("a").each(function() {
		   var category_id = jQuery(this).attr("id").split('_').pop(); //creates closure for click event
		   jQuery(this).click(function() {
		   jQuery("#attColumn").removeClass();
		   jQuery('#valColumn').html(" ").attr("class","empty");
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
	jQuery("#attColumn").html("<div class='loading'>Loading Attributes...</div>");
	var url = jQuery("#attColumn").attr("class");
	jQuery.get(url,function(data) {
			jQuery("#attColumn").html(data);
			Dase.bindGetValues(coll);
			});
	if (jQuery("#attColumn").attr("class")) {
		//meaning there is an attr_id embedded in the className of attColumn
		jQuery("#valColumn").removeClass();
	} else {
		jQuery('#valColumn').html(" ").attr("class","empty");
	}
	jQuery('#autocomplete').html(" ");
}

Dase.getAdminAttributes = function(coll) {
	var params = {
token: new Date().getTime(),
	   link_class: "att_link",
	   coll: coll
	};	   
	jQuery("#catColumn < a[class=spill]").attr("class","catLink");
	jQuery("#catLink_admin").attr("class","spill");
	jQuery("#attColumn").html("<div class='loading'>Loading Admin Attributes...</div>");
	jQuery.get("ajax/admin_attributes",params,function(data) {
			jQuery("#attColumn").html(data);
			Dase.bindGetValues(coll);
			});
	if (jQuery("#attColumn").attr("class")) {
		//meaning there is an attr_id embedded in the className of attColumn
		jQuery("#valColumn").removeClass();
	} else {
		jQuery('#valColumn').html(" ").attr("class","empty");
	}
	jQuery('#autocomplete').html(" ");
}

Dase.bindGetValues = function(coll) {
	jQuery("#attColumn").find('a').each(function() {
			var attribute_id = jQuery(this).attr("class").split(" ").pop(); //creates closure for click event
			jQuery(this).click(function() {
				var params = {
token: new Date().getTime(),
attribute_id: attribute_id,
coll: coll
};	   
jQuery("#attColumn//a[class=spill]").attr("class","att_link");
jQuery("#att_link_"+attribute_id).attr("class","spill");
jQuery("#valColumn").html("<div class='loading'>Loading Values...</div>");
jQuery.get("ajax/values_by_attribute",params,function(data) {
	jQuery("#valColumn").html(data).removeClass();
	});
jQuery('#autocomplete').html(" ");
window.scroll(0,0);
return false;
});
			});
Dase.getAttributeTallies(coll);
}

Dase.getAttributeTallies = function(coll) {
	if (jQuery("#getTallies").size()) {
		var params = {
token: new Date().getTime(),
	   coll: coll
		};	   
		if ('adminAtts' == jQuery('#getTallies').attr("class")) { 
			params.admin = 1;
		}
		if (jQuery('#currently_using_cb').size()) {
			params.cb=1;
		}
		jQuery.get("ajax/attribute_tallies",params,function(data) {
				jQuery("attribute",data).each(function() {
					if (0 == jQuery(this).attr('val_tally')) {
					//this makes attributes that have 0 values disappear!!!!
					jQuery('#att_link_'+jQuery(this).attr("id")).hide();
					} else {
					jQuery('#tally-'+jQuery(this).attr("id")).text("(" + jQuery(this).attr('val_tally') + ")");
					}
					});

				});
	}
}

Dase.initDynamicSearchForm = function() {
	jQuery("select.dynamic").change(function() {
			jQuery(this).parent().find("input[type=text]").attr("name",jQuery("option:selected",this).attr("value"));
			});
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
