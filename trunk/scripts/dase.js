//define the Dase namespace
if (!Dase) {
	var Dase = {};
}

Dase.prepareRemoteLaunch = function(id) {
	var launcher = document.getElementById(id);
	if (launcher) {
		var params = launcher.className.split(" ");
		var tag_id = params[0];
		launcher.onclick = function() { 
			window.name = "slideshow"; 
			var new_window = "scrollbars,resizable,width=133,height=600,left=700,top=25";
			OpenWindow = window.open("view/slideshow_controller/" + tag_id, "remote" + tag_id, new_window);
			return false;
		}
	}
	return true;
}

function EditAnnotation(divid) {
	edit = "annotationForm" + divid;
	link = "annotation" + divid;
	Dase.toggle(edit);
	Dase.toggle(link);
	return false;
}

Dase.confirmSubmit = function(confirm_id) {
	var elem = document.getElementById(confirm_id);
	var confirm_val = elem.value;
	var confirm_name = elem.name;
	if (confirm_val) {
	return confirm(confirm_name + ' is set to ' + confirm_val + '. Is this what you intend?');
	} else {
		alert('Please enter a value for ' + confirm_value);
		return false;
	}
}

function confirmDelete(theElement) {
	var agree=confirm('Think carefully...you are about to delete\n' + theElement);
	if (agree) {
		return true;
	}
	return agree;
}

Dase.confirmCopyright = function() {
	var statement='*****COPYRIGHT NOTICE*****\n\nMaterial on this site may be used for educational purposes by current faculty, students, and staff of the University of Texas at Austin. This site provides access to materials, licensed or otherwise, for which the copyright is held by owners other than the University of Texas at Austin. Use of these materials and resources is restricted by applicable license agreement and copyright law.';
	var agree=confirm(statement);
	if (agree) {
		return true;
	}
	return agree;
}

Dase.createTag = function(type_id,type){
	var name;
	name = prompt("Please enter a title for your " + type);
	if ((name==null) || (name.length == 0)) { 
		return false;
	}
	var createTagForm = document.getElementById('createTagForm');
	createTagForm.name.value = name;
	createTagForm.tag_type_id.value = type_id;
	createTagForm.submit();
	return false;
}

function sortSlides(static_slide_id,static_slide_num){

	var checked = false;
	for (var i = 0; i < document.sortSlidesForm.checks.length; i++) {
		if (document.sortSlidesForm.checks[i].checked == true) { 
			checked = true; 
		} 
	}
	if (checked == false) {
		alert("You must select at least one image to move.");
		return;
	}
	var agree=confirm("You are about to move all of the selected images after slide " + static_slide_num + ".");
	if (agree) {
		document.sortSlidesForm.static_slide_id.value = static_slide_id;
		document.sortSlidesForm.submit();
	}
}
function SortSlidesJs(tagId) {
	var orderArray = new Array();
	node=document.getElementsByName('sort_order');
	for (var i=0;i<node.length;i++) {
		orderArray[i]=node[i].value;
	}
	orderStr = orderArray.join();
	document.sortSlidesForm.new_order_str.value = orderStr;
	document.sortSlidesForm.submit();
	return false;
}


function execute(page_with_link){
	window.location = page_with_link;
}

function popValue(id,value){
	document.getElementById(id).value = value;
	return false;
}

/************* dase_ajax.js *****************************/

Dase.htmlspecialchars = function(str) {
	str = str.replace(/</g,"&lt;");
	str = str.replace(/>/g,"&gt;");
	return str;
}

var xmlhttp = null;

Dase.getHtml = function(url,elem_id,my_func) {
	var target = document.getElementById(elem_id);
	if (target) {
		target.innerHTML = '<div class="loading">Loading...</div>';
	}
	Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
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

Dase.getEscapedHtml = function(url,elem_id,my_func) {
	var target = document.getElementById(elem_id);
	if (target) {
		target.innerHTML = '<div class="loading">Loading...</div>';
	}
	Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var returnStr = xmlhttp.responseText;
			document.getElementById(elem_id).innerHTML = Dase.htmlspecialchars(returnStr);
			if (my_func) {
				my_func();
			}
		} else {
			// wait for the call to complete
		}
	}
}






/***** HERE xhr are stored in an array *********/

var xmlreqs = new Array();

/* this is clever, but I suspect there is ALWAYS a more efficient way by making
 * one complex query, putting the result in xml, and let javascript parse and place it
 */

Dase.getHtmlSerial = function(ajax_url,elem_id) {
	var xmlhttp = false;
	var xmlhttp = Dase.createXMLHttpRequestSer();
	xmlhttp.open('GET', ajax_url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (typeof(window['xmlreqs']) == "undefined") return;
		for (var i=0; i < xmlreqs.length; i++)
		{
			if (xmlreqs[i].xmlhttp.readyState == 4)
			{
				if (xmlreqs[i].xmlhttp.status == 200 || xmlreqs[i].xmlhttp.status ==
						304)
				{
					//200 OK
					if (document.getElementById(xmlreqs[i].elem_id)) { //'cause it could've been destroyed
						document.getElementById(xmlreqs[i].elem_id).innerHTML = xmlreqs[i].xmlhttp.responseText;
						xmlreqs.splice(i,1);i--;
					}
				}
				else
				{
					// error
					xmlreqs.splice(i,1); i--;
					//alert("A problem occurred!");
				}
			}
		} 
	}
	var xmlreq = {type:"",xmlhttp:xmlhttp,elem_id:elem_id};
	xmlreqs.push(xmlreq);
}

Dase.createXMLHttpRequestSer = function() {
	var xmlhttp = false;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		alert('Perhaps your browser does not support xmlhttprequests?');
	}
	return xmlhttp;
}

Dase.clearHtml = function(elem_id) {
	document.getElementById(elem_id).innerHTML = '';
}

Dase.createXMLHttpRequest = function() {
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		alert('Perhaps your browser does not support xmlhttprequests?');
	}
}

/*
 *     Written by Jonathan Snook, http://www.snook.ca/jonathan
 *         Add-ons by Robert Nyman, http://www.robertnyman.com
 *         */

function getElementsByClassName(oElm, strTagName, strClassName){
	var arrElements = (strTagName == "*" && document.all)? document.all : oElm.getElementsByTagName(strTagName);
	var arrReturnElements = new Array();
	strClassName = strClassName.replace(/\-/g, "\\-");
	var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)");
	var oElement;
	for(var i=0; i<arrElements.length; i++){
		oElement = arrElements[i];      
		if(oRegExp.test(oElement.className)){
			arrReturnElements.push(oElement);
		}   
	}
	return (arrReturnElements)
}

Dase.prepareAddFileUpload = function() {
	var addLink = document.getElementById('addFileInput');
	if (addLink) {
		addLink.onclick = function() {
			var newInput = document.createElement('input');
			newInput.setAttribute('type','file');
			newInput.setAttribute('size','45');
			newInput.setAttribute('name','upload_files[]');
			var target = document.getElementById('newInputTarget');
			if (target) {
				var num = target.getElementsByTagName('input').length+1;
				if (num > 2) {
					alert('Uploading more than '+num+' images or more than 10Mb total is not recommended, although you are free to give it a shot...')
				};
				target.appendChild(newInput);
			}
			Dase.prepareUploadValidation();
			return false;
		}
	}
}

Dase.prepareClearCheck = function(formId) {
	if (!document.getElementsByTagName) return false;
	if (!document.getElementById) return false;
	if (!document.getElementById(formId)){
		return false;
	}
	var checkAllId = formId + 'CheckAll';
	var clearAllId = formId + 'ClearAll';
	var checkAllElem = document.getElementById(checkAllId);
	var clearAllElem = document.getElementById(clearAllId);
	if (checkAllElem) {
		checkAllElem.onclick = function() {
			Dase.checkAll(formId);
			return false;
		}
	}
	if (clearAllElem) {
		clearAllElem.onclick = function() {
			Dase.clearAll(formId);
			return false;
		}
	}
	return true;
}

Dase.cartAdds = function(url,elem_id,toggleClass,cart_id) {
	var ident = toggleClass+'_'+elem_id;
	var toggle = document.getElementById(ident);
	toggle.className = toggleClass+' '+elem_id;
	Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var tally = document.getElementById('tagCountNav_'+cart_id);
			if (tally) {
				var total =  tally.innerHTML;
				if ('addToCart' == toggleClass) {
					tally.innerHTML = total-1;
				} 
				if ('removeFromCart' == toggleClass) {
					total++;
					tally.innerHTML = total;
				}
			}
			Dase.prepareCartAdds();
		} else {
			// wait for the call to complete
		}
	}
}

Dase.ajaxDelete = function(url,doomed_id,decrement_id) {
	var doomed = document.getElementById(doomed_id);
	doomed.className = "cue";
	Dase.createXMLHttpRequest(); //had to put constructor here so key-up functions work
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var parent = doomed.parentNode;
			parent.removeChild(doomed);
			var msg = document.getElementById('deleteMsg')
				msg.innerHTML = xmlhttp.responseText;
			var tally = document.getElementById('tagCountNav_'+decrement_id);
			if (tally) {
				var total =  tally.innerHTML;
				tally.innerHTML = total-1;
			}
			var tally2 = document.getElementById('tagCountPage_'+decrement_id);
			if (tally2) {
				var total2 =  tally2.innerHTML;
				tally2.innerHTML = total2-1;
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.prepareDeletable = function() {
	var deletable = document.getElementById('deletable');
	if (deletable) {
		var deletes = deletable.getElementsByTagName('a');
		if (deletes) {
			for (var i=0;i<deletes.length;i++) {
				var del = deletes[i];
				var classes = (del.className).split(" ");
				if (classes && classes.length>=2
						&& classes[0]=="delete") {
					var table = classes[1];
					var pk = classes[2];
					var tag_id = classes[3];
					del.table = table;
					del.pk = pk;
					del.tag_id = tag_id;
					del.onclick = Dase.constructDelete;
				}
			}
		}
	}
}

Dase.prepareAttributeFlags = function() {
	var attTable = document.getElementById('attTable');
	if (attTable) {
		var atts = attTable.getElementsByTagName('td');
		if (atts) {
			for (var i=0;i<atts.length;i++) {
				var att = atts[i];
				var classes = (att.className).split(" ");
				if (classes && classes[0]=="flag") {
					var attribute_id = classes[1];
					var element = classes[2];
					att.attribute_id = attribute_id;
					att.element = element;
					att.onclick = Dase.constructToggleAttributeFlag;
				}
			}
		}
	}
}

Dase.constructToggleAttributeFlag = function(event) {
	var att_id = this.attribute_id;
	var element = this.element;
	var att_flags = document.getElementById('flags_'+att_id);
	att_flags.id = 'update_me';
	var url = "service/toggleAttFlag.php";
	var params = "?att_id="+att_id;
	params += "&element="+element;
	var target = 'update_msg';
	Dase.getHtml(url+params,target,Dase.updateAttributeFlags);
	return false;
}

Dase.updateAttributeFlags = function() {
	var pending = document.getElementById('update_me');
	var att_id = pending.className;
	pending.id = 'flags_'+att_id;
	var elements = ["basic","list","public"];
	for (var i=0;i<elements.length;i++) {
		var element = elements[i];
		var target = element + '_' + att_id;
		var att_flag = document.getElementById(target);
		var url = "service/getAttFlag.php";
		var params = "?att_id="+att_id;
		params += "&element="+element;
		Dase.getHtmlSerial(url+params,target);
	}
	Dase.prepareAttributeFlags();
}

Dase.prepareCartAdds = function() {
	var ca = document.getElementById('cartAdds');
	if (ca) {
		var tag_id = ca.className;
		var cartables = ca.getElementsByTagName('a');
		if (cartables) {
			for (var i=0;i<cartables.length;i++) {
				var car = cartables[i];
				var classes = (car.className).split(" ");
				if (classes && classes[0]=="addToCart") {
					var item_id = classes[1];
					car.item_id = item_id;
					car.tag_id = tag_id;
					car.onclick = Dase.constructAddToCart;
				}
				if (classes && classes[0]=="removeFromCart") {
					var item_id = classes[1];
					car.item_id = item_id;
					car.tag_id = tag_id;
					car.onclick = Dase.constructRemoveFromCart;
				}
			}
		}
	}
}

Dase.constructAddToCart = function(event) {
	var ident = 'addToCart_'+this.item_id;
	var hide = document.getElementById(ident);
	hide.className += " hide";
	var url = "service/cart_add.php";
	var params = "?tag_id="+this.tag_id;
	params += "&item_id="+this.item_id;
	Dase.cartAdds(url+params,this.item_id,'removeFromCart',this.tag_id);
	return false;
}

Dase.constructRemoveFromCart = function(event) {
	var ident = 'removeFromCart_'+this.item_id;
	var hide = document.getElementById(ident);
	hide.className += " hide";
	var url = "service/cart_remove.php";
	var params = "?tag_id="+this.tag_id;
	params += "&item_id="+this.item_id;
	Dase.cartAdds(url+params,this.item_id,'addToCart',this.tag_id);
	return false;
}

Dase.constructDelete = function(event) {
	//BEWARE: IE6 might not use 'this'
	var pk = this.pk;
	var decrement_id = this.tag_id;
	var table = this.table;
	var url = "service/delete.php";
	var params = "?table="+table;
	params += "&pk="+pk;
	var doomed_id = 'deletable_'+pk;
	Dase.ajaxDelete(url+params,doomed_id,decrement_id);
	return false;
}

Dase.prepareDownload = function(id) {
	var d = document.getElementById(id);
	if (d) {
		d.onclick = function() {
			return Dase.confirmCopyright();
		}
	}
}

Dase.setAutoReload = function() {
	//what if user has many browsers open on many machines? 
	//this could cause cb to be deleted on another mache, so 
	//they get bumped on ANY machine
	//so we'll limit to browsers that are USING cb
	//so the bump will be idempotent (i.e., multiple occurances
	//make no further state stange beyond first occurrance).
	var cb = document.getElementById('currently_using_cb');
	if (cb) {
		//reload every 20 minutes
		setTimeout('document.location.reload()',60*1000*20);
	}
}

Dase.prepareUploadValidation = function() {
	var uploadForm = document.getElementById('uploadForm');
	if (uploadForm) { 
		uploadForm.onsubmit = function() {
			fileInputs = document.getElementsByName('upload_files[]');
			if (fileInputs) {
				for (var i = 0; i< fileInputs.length; i++) {
					var fileName = fileInputs[i].value;
					if (fileName && !Dase.validateFile(fileName)) {
						return false;
					} 
				}
				return true;
			}
			return false; //no file inputs
		}
	}
	return false; //no uploadForm 
}

Dase.validateFile = function(fileName) {
	var ext = fileName.slice(-3).toLowerCase();
	if (
			(ext != 'jpg') &&
			(ext != 'jpeg') &&
			(ext != 'gif') &&
			(ext != 'png') &&
			(ext != 'tif') &&
			(ext != 'mp3') &&
			(ext != 'mov') &&
			(ext != 'doc') &&
			(ext != 'aiff') &&
			(ext != 'txt') &&
			(ext != 'xml') &&
			(ext != 'xslt') &&
			(ext != 'html') &&
			(ext != 'qt') &&
			(ext != 'css') &&
			(ext != 'wav') &&
			(ext != 'pdf') 
	   ) {
		alert(fileName + ' appears to be an invalid media file.  Media files must have the proper extension (jpg,gif,png,tif,mp3,mov,doc,aiff,txt,xml,xslt,html,qt,css,wav,pdf)');
		return false;
	} 
	return true;
}

Dase.prepareHelpModule = function () {
	var help_link = document.getElementById('helpModule');
	if (help_link) {
		help_link.onclick = function() {
			var win = window.open('apps/help', 'helpWindow', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=500,height=400');
			win.focus();
			return false;
		}
	}
}

Dase.prepareHelpPopup = function() {
	var help_link = document.getElementById('helpPopup');
	if (help_link) {
		help_link.onclick = function() {
			var win = window.open(this.href, 'helpWindow', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=700,height=700');
			win.focus();
			return false;
		}
	}
}

Dase.prepareMine = function() {
	var mine = document.getElementById('mine');
	if (mine) {
		mine.onclick = function() {
			alert("You cannot subscribe to this collection because you already own it!");
			return false;
		}
	}
}

Dase.prepareMediaLinks = function() {
	var media_links = document.getElementById('mediaLinks');
	if (media_links) {
		var links = media_links.getElementsByTagName('a');
		if (links) {
			for (var i=0;i<links.length;i++) {
				var link = links[i];
				var classes = (link.className).split(" ");
				if (classes && classes[0]=="image") {
					link.width = Number(classes[1])+30;
					link.height = Number(classes[2])+30;
					link.size = classes[3];
					link.href;
					link.onclick = Dase.mediaPopUp; 
				}
			}
		}
	}
}

Dase.prepareDeleteCommonValues = function() {
	var common_form = document.getElementById('deleteCommonValuesForm');
	if (common_form) {
		common_form.onsubmit = function() {
			return confirm('You are about to delete all instances of this attribute/value pair in this admin collection. Is this what you intend?  If so, proceed.  Otherwise cancel.'); 
		}
	}
}

Dase.prepareTagItems = function() {
	var set = document.getElementById('tag_item_targets');
	if (set) {
		var tag_items = set.getElementsByTagName('div');
		if (tag_items) {
			for (var i=0;i<tag_items.length;i++) {
				var tagit = tag_items[i];
				var tag_item_id = tagit.className;
				var url = "service/getTagItem.php";
				var params = "?tag_item_id="+tag_item_id;
				Dase.getHtmlSerial(url+params,"tag_item_target_"+tag_item_id);
			}
		}
	}
}

Dase.mediaPopUp = function(event) {
	var win = window.open(this.href,this.size,'toolbar=0,scrollbars=1,location=o,statusbar=0,menubar=0,resizable=1,width='+this.width+',height='+this.height);
	win.focus();
	return false;
}

Dase.prepareLinkBack = function() {
	var lb = document.getElementById('linkBack');
	if (lb) {
		lb.onclick = function () {
			var win = window.open(this.href,this.size,'toolbar=0,scrollbars=0,location=o,statusbar=0,menubar=0,resizable=1,width=770,height=300');
			win.focus();
			return false;
		}
	}
}

/**********DASE EDITABLE**********************/

Dase.prepareAddMetadata = function(id) {
	var mform = document.getElementById(id);
	var buttons = document.getElementById('addMetadataButtons');
	if (buttons) {
		buttons.innerHTML = '';
	}
	var inputs = document.getElementById('addMetadataInputs');
	if (inputs) {
		inputs.innerHTML = '';
	}
	var usageNotes = document.getElementById('usageNotes');
	if (usageNotes) {
		usageNotes.innerHTML = '';
	}
	if (mform) {
		var select = mform.getElementsByTagName('select')[0];
		if (select) {
			select.selectedIndex = 0;
			select.onchange = Dase.getHtmlInput; 
		}
	}
}

Dase.getHtmlInput = function() {
	var radio_buttons = 1;
	var checkboxes = 2;
	var select_menu = 3;
	var textbox_with_dynamic_menu = 4;
	var textbox = 5;
	var textarea = 6;
	var list_box = 7;
	var non_editable = 8;
	var opt = this.options[this.selectedIndex];
	var vals = opt.value.split(" ");
	if (vals) {
		var att_id = vals[0];
		var html_id = Number(vals[1]);
	}
	if (!html_id) {
		html_id = 5;
	}
	var button_area = document.getElementById('addMetadataButtons');
	if (button_area) {
		button_area.innerHTML = '';
	}
	var input_area = document.getElementById('addMetadataInputs');
	if (input_area) {
		input_area.innerHTML = '';
	}
	var usageNotes = document.getElementById('usageNotes');
	if (usageNotes) {
		usageNotes.innerHTML = '';
	}
	switch (html_id) {
	case radio_buttons:
		Dase.getRadioButtons(att_id,input_area);
		break;
	case checkboxes:
		Dase.getCheckboxes(att_id,input_area);
		break;
	case select_menu:
		var select_elem = document.createElement("select");
		select_elem.setAttribute("name","new_val");
		select_elem.className = "waiting";
		input_area.appendChild(select_elem);
		Dase.getSelectValues('defined',att_id,select_elem,'select one:');
		break;
	case textbox: 
		var text_in = document.createElement("input");
		text_in.setAttribute("name","new_val");
		text_in.setAttribute("size",'32');
		text_in.setAttribute("type","text");
		input_area.appendChild(text_in);
		Dase.getUsageNotes(att_id);
		break;
	case textarea: 
		var text_in = document.createElement("textarea");
		text_in.setAttribute("name","new_val");
		text_in.setAttribute("cols","32");
		text_in.setAttribute("rows","5");
		input_area.appendChild(text_in);
		Dase.getUsageNotes(att_id);
		break;
	case non_editable: 
		var text_in = document.createElement("textarea");
		text_in.setAttribute("name","new_val");
		text_in.setAttribute("cols","32");
		text_in.setAttribute("rows","4");
		input_area.appendChild(text_in);
		break;
	case list_box: 
		var text_in = document.createElement("textarea");
		var inp_type = document.createElement("input");
		inp_type.setAttribute('name','input_type');
		inp_type.setAttribute('value','list_box');
		inp_type.setAttribute('type','hidden');
		input_area.appendChild(inp_type);
		text_in.setAttribute("name","new_val");
		text_in.setAttribute("cols","32");
		text_in.setAttribute("rows","5");
		input_area.appendChild(text_in);
		Dase.getUsageNotes(att_id);
		break;
	case textbox_with_dynamic_menu:
		var text_in = document.createElement("input");
		text_in.setAttribute("name","new_val");
		text_in.setAttribute("size",'32');
		text_in.setAttribute("type","text");
		input_area.appendChild(text_in);
		var br = document.createElement("br");
		input_area.appendChild(br);
		var select_elem = document.createElement("select");
		select_elem.className = "waiting";
		select_elem.onchange = function() {
			text_in.value = this.options[this.selectedIndex].value;
		}
		input_area.appendChild(select_elem);
		Dase.getSelectValues('dynamic',att_id,select_elem,'browse existing values:');
		break;
	default:
		var text_in = document.createElement("input");
		text_in.setAttribute("name","new_val");
		text_in.setAttribute("size",'32');
		text_in.setAttribute("type","text");
		input_area.appendChild(text_in);
		Dase.getUsageNotes(att_id);
		break;
	}

	if (button_area) {
		var save = document.createElement("input");
		/* NOTE: for IE6 need to set type before value****/
		save.setAttribute("type","submit");
		save.setAttribute("value","add");
		save.setAttribute("class","savbtn");
		button_area.appendChild(save);
		button_area.appendChild(document.createTextNode(' '));

		var cancel = document.createElement("input");
		cancel.setAttribute("type","submit");
		cancel.setAttribute("value","cancel");
		cancel.setAttribute("class","btn");
		cancel.onclick = function() {
			Dase.prepareAddMetadata();
			return false;
		}
		button_area.appendChild(cancel);
	}
}

Dase.prepareEditable = function() {
	var editable = document.getElementById('editable');
	if (editable) {
		var edits = editable.getElementsByTagName('div');
		if (edits) {
			var textarea_width = Dase.getTextareaWidth();
			for (var i=0;i<edits.length;i++) {
				var edit = edits[i];
				var classes = (edit.className).split(" ");
				if (classes && classes.length>2
						&& classes[0]=="edit") {
					var value_id = classes[1];
					var attribute_id = classes[2];
					var html_id = classes[3];
					edit.textarea_width = textarea_width;
					edit.id=value_id;
					edit.html_id=html_id;
					edit.att_id=attribute_id;
					edit.onclick = Dase.getEditForm;
				} 
			}
		}
	}
}

Dase.prepareGenericEditable = function() {
	var editable = document.getElementById('genericEditable');
	if (editable) {
		var edits = editable.getElementsByTagName('div');
		if (edits) {
			for (var i=0;i<edits.length;i++) {
				var edit = edits[i];
				var classes = (edit.className).split(" ");
				if (classes && classes.length>=2
						&& classes[0]=="edit") {
					var table = classes[1];
					var column = classes[2];
					var pk = classes[3];
					edit.table = table;
					edit.column = column;
					edit.pk=pk;
					edit.id=table+column+pk;
					edit.onclick = Dase.getGenericEditForm;
				}
			}
		}
	}
}

Dase.getGenericEditForm = function(event) {
	var original = this;
	var table = this.table;
	var column = this.column;
	var pk = this.pk;
	var target_id = this.id;

	//per quirksmode blog, ie6 does not understand 'this'
	//like other browsers (i think) so I cannot use it to disable the 
	//onclick event . gotta come up w/ another way...
	//ok fixed it by manipulating className
	var orig_class = this.className;

	var form_id = 'form-'+this.id;
	var current = this.childNodes[0].nodeValue;
	if (!current) {
		current = this.innerHTML;
	}
	this.innerHTML = '';
	var form = document.createElement("form");
	form.setAttribute("id",form_id);
	form.setAttribute("class","styled");
	form.setAttribute("method","post");
	form.setAttribute("action","index.php");

	var pk_in = document.createElement("input");
	pk_in.setAttribute("name",table+"_id");
	pk_in.setAttribute("type","hidden");
	pk_in.setAttribute("value",pk);
	form.appendChild(pk_in);

	var text_in = document.createElement("textarea");
	text_in.setAttribute("name","new_val");
	text_in.setAttribute("rows",2);
	text_in.setAttribute("cols",50);
	text_in.appendChild(document.createTextNode(current));
	form.appendChild(text_in);

	var br = document.createElement("br");
	form.appendChild(br);
	var save = document.createElement("input");
	/* NOTE: for IE6 need to set type before value****/
	save.setAttribute("type","submit");
	save.setAttribute("value","save");
	save.setAttribute("name","save");
	save.setAttribute("class","savbtn");
	save.onclick = function(e) {
		if (text_in) {
			var url = "service/update_generic.php";
			var params = "?table="+table;
			params += "&column="+column;
			params += "&pk="+pk;
			params += "&new_val="+encodeURIComponent(text_in.value);
			Dase.getEscapedHtml(url+params,target_id,Dase.prepareGenericEditable);
			/* had to do the following for safari else getEditForm ran on cancel! */
		} else {
			return true;
		}
		if ( !e && window.event ){
			e = window.event;
		}
		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		original.onclick = Dase.getGenericEditForm;
		return false;
	}
	form.appendChild(save);

	form.appendChild(document.createTextNode(' '));

	var cancel = document.createElement("input");
	cancel.setAttribute("type","submit");
	cancel.setAttribute("value","cancel");
	cancel.setAttribute("class","btn");
	cancel.onclick = function(e) {
		original.className = orig_class;
		original.innerHTML = Dase.htmlspecialchars(current);

		/* had to do the following for safari else getEditForm ran on cancel! */
		if ( !e && window.event ){
			e = window.event;
		}
		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		original.onclick = Dase.getGenericEditForm;
		return false;
	}
	form.appendChild(cancel);

	form.appendChild(document.createTextNode(' '));

	this.appendChild(form);
	this.className = 'updated';
	original.onclick = null;
	return false;
}

/** EDITING FORM BEGINS ******************/

Dase.getEditForm = function(event) {
	var collection_ascii_id = document.getElementById('collection_ascii_id').content;
	var original = this;
	var value_id = this.id;
	var att_id = this.att_id;
	var html_id = Number(this.html_id);

	//per quirksmode blog, ie6 does not understand 'this'
	//like other browsers (i think) so I cannot use it to disable the 
	//onclick event . gotta come up w/ another way...
	//ok fixed it by manipulating className
	var orig_class = this.className;

	var form_id = 'form-'+value_id;
	var current = this.childNodes[0].nodeValue;
	if (!current) {
		current = this.innerHTML;
	}
	var str_length = current.length;
	this.innerHTML = '';
	var form = document.createElement("form");
	form.setAttribute("id",form_id);
	form.setAttribute("class","styled");
	form.setAttribute("method","post");
	form.setAttribute("action","index.php");

	var pk_in = document.createElement("input");
	pk_in.setAttribute("name","value_id");
	pk_in.setAttribute("type","hidden");
	pk_in.setAttribute("value",value_id);
	var att_in = document.createElement("input");
	att_in.setAttribute("name","orig_att_id");
	att_in.setAttribute("type","hidden");
	att_in.setAttribute("value",att_id);
	form.appendChild(att_in);
	var action_in = document.createElement("input");
	action_in.setAttribute("name","action");
	action_in.setAttribute("type","hidden");
	action_in.setAttribute("value","update_value");
	form.appendChild(action_in);
	var value_in = document.createElement("input");
	value_in.setAttribute("name","value_id");
	value_in.setAttribute("type","hidden");
	value_in.setAttribute("value",value_id);
	form.appendChild(value_in);

	/* grab the attribute select pulldown that's already on the page  */
	var att_select = document.getElementById('attribute_select');
	var new_att_select = att_select.cloneNode(true);
	var att_options = new_att_select.getElementsByTagName('option');

	for (var i=0;i<att_options.length;i++) {
		var opt = att_options[i];
		opt.removeAttribute('selected');
		var vals = opt.value.split(" ");
		if (vals) {
			var opt_att_id = vals[0];
		}
		if ((att_id) && (opt_att_id == att_id)) {
			opt.setAttribute('selected','');
			opt.selected = 'selected';
			var valid_opts = true;
		}
	}
	new_att_select.onchange = function() {
		if (confirm('Are you sure that you want to change the attribute for this value?')) {
			save.onclick = null;
		} else {
			var att_options = this.getElementsByTagName('option');
			for (var i=0;i<att_options.length;i++) {
				var opt = att_options[i];
				opt.removeAttribute('selected');
				var vals = opt.value.split(" ");
				if (vals) {
					var opt_att_id = vals[0];
				}
				if ((att_id) && (opt_att_id == att_id)) {
					opt.setAttribute('selected','');
					opt.selected = 'selected';
				}
			}
		}
	}

	/* here's where we provide a radio button set if the att has defined values */
	var radio_buttons = 1;
	var checkboxes = 2;
	var select_menu = 3;
	var textbox_with_dynamic_menu = 4;
	var textbox = 5;
	var textarea = 6;
	var list_box = 7;
	var non_editable = 8;

	switch (html_id) {
	case select_menu:
		Dase.getRadioButtonsEdit(att_id,form,original,current);
		break;
	case radio_buttons:
		Dase.getRadioButtonsEdit(att_id,form,original,current);
		break;
	case checkboxes:
		Dase.getRadioButtonsEdit(att_id,form,original,current);
		break;
	default:
		if (valid_opts) {
			form.appendChild(new_att_select);
			var br = document.createElement("br");
			form.appendChild(br);
		}
		var copyable = 1;
		var newline_pattern = /\n/;
		/* first, place the previously constructed select menu */
		/* create either a textbox or textarea depending on value_text size */
		if (str_length < 50 && !current.match(newline_pattern)) {
			var text_in = document.createElement("input");
			text_in.setAttribute("name","new_val");
			text_in.setAttribute("type","text");
			text_in.setAttribute("size",str_length+2);
			text_in.setAttribute("value",current);
			form.appendChild(text_in);
		} else {
			var text_in = document.createElement("textarea");
			text_in.setAttribute("name","new_val");
			text_in.style.width = this.textarea_width + 'px';
			var rows = Math.ceil(str_length/(this.textarea_width/8))+5;
			text_in.setAttribute("rows",rows);
			text_in.appendChild(document.createTextNode(current));
			form.appendChild(text_in);
		}
		break;
	}

	var br = document.createElement("br");
	form.appendChild(br);
	var save = document.createElement("input");
	/* NOTE: for IE6 need to set type before value****/
	save.setAttribute("type","submit");
	save.setAttribute("value","save");
	save.setAttribute("name","save");
	save.setAttribute("class","savbtn");
	save.onclick = function(e) {
		if (text_in) {
			var url = "service/update.php";
			var params = "?value_id="+value_id;
			params += "&att_id="+att_id;
			params += "&new_val="+encodeURIComponent(text_in.value);
			Dase.getEscapedHtml(url+params,value_id);
			original.className = orig_class + ' updated';
			/* had to do the following for safari else getEditForm ran on cancel! */
		} else {
			return true;
		}
		if ( !e && window.event ){
			e = window.event;
		}
		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		original.onclick = Dase.getEditForm;
		return false;
	}
	form.appendChild(save);

	if (copyable) {
		form.appendChild(document.createTextNode(' '));

		var copy = document.createElement("input");
		/* NOTE: for IE6 need to set type before value****/
		/* copy actually submits the form (submit is not hijacked) */
		copy.setAttribute("type","submit");
		copy.setAttribute("value","copy");
		copy.setAttribute("name","copy");
		copy.setAttribute("class","copybtn");
		form.appendChild(copy);
	}

	form.appendChild(document.createTextNode(' '));

	var cancel = document.createElement("input");
	cancel.setAttribute("type","submit");
	cancel.setAttribute("value","cancel");
	cancel.setAttribute("class","btn");
	cancel.onclick = function(e) {
		original.className = orig_class;
		original.innerHTML = Dase.htmlspecialchars(current);

		/* had to do the following for safari else getEditForm ran on cancel! */
		if ( !e && window.event ){
			e = window.event;
		}
		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		original.onclick = Dase.getEditForm;
		return false;
	}
	form.appendChild(cancel);

	form.appendChild(document.createTextNode(' '));

	var del = document.createElement("input");
	del.setAttribute("type","submit");
	del.setAttribute("value","delete");
	del.setAttribute("class","delbtn");
	del.onclick = function() {
		var url = "service/update.php";
		var params = "?value_id="+value_id;
		params += "&att_id="+att_id;
		params += "&delete=delete";
		Dase.getHtml(url+params,value_id,Dase.prepareEditable);
		original.className = 'deleted';
		original.onclick = null;
		return false;
	}
	form.appendChild(del);

	this.appendChild(form);
	this.className = 'inprocess';
	original.onclick = null;
	return false;
}

Dase.getSelectValues = function(type,attribute_id,select_target,completed_txt) {
	var first_option = document.createElement("option");
	var load_msg = document.createTextNode('loading data...');
	var complete_msg = document.createTextNode(completed_txt);
	first_option.appendChild(load_msg);
	select_target.appendChild(first_option);
	var url = "service/getAttributeValues.php";
	var params = "?type="+type;
	params += "&attribute_id="+attribute_id;
	Dase.createXMLHttpRequest(); 
	xmlhttp.open('GET', url+params, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			first_option.removeChild(load_msg);
			first_option.appendChild(complete_msg);
			select_target.className = "show";
			var xml = xmlhttp.responseXML;
			var values = xml.getElementsByTagName('value');
			if (values) {
				for (var i = 0; i < values.length; i++) {
					if (values[i].firstChild) {
						var opt = document.createElement('option');
						var displayVal = values[i].firstChild.nodeValue;
						if ('dynamic' == type) {
							if (displayVal.length > 50) {
								displayVal = displayVal.substring(0,46) + '...';
							}
							opt.setAttribute('value',displayVal);
						} else {
							opt.setAttribute('value',displayVal);
							//opt.setAttribute('value',values[i].getAttribute('id'));
						}
						opt.appendChild(document.createTextNode(displayVal));
						select_target.appendChild(opt);
					}
				}
				var usageNotes = document.getElementById('usageNotes');
				if (usageNotes && xml.getElementsByTagName('usage')[0]) {
					var usageNode = xml.getElementsByTagName('usage')[0];
					if (usageNode.firstChild) {
						usageNotes.appendChild(document.createTextNode('Usage Notes: '+usageNode.firstChild.nodeValue));
					}
				}
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.getUsageNotes = function(attribute_id,target) {
	var url = "service/getAttributeValues.php";
	var params = "?type=defined";
	params += "&attribute_id="+attribute_id;
	Dase.createXMLHttpRequest(); 
	xmlhttp.open('GET', url+params, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var xml = xmlhttp.responseXML;
			if (xml) {
				var usageNotes = document.getElementById('usageNotes');
				if (usageNotes && xml.getElementsByTagName('usage')[0]) {
					var usageNode = xml.getElementsByTagName('usage')[0];
					usageNotes.appendChild(document.createTextNode('Usage Notes: '+usageNode.firstChild.nodeValue));
				}
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.getRadioButtonsEdit = function(attribute_id,form,original,current) {
	/* we create our target here, because if we simply append
	 * to for after function returns, the result gets placed AFTER th submit buttons
	 */
	var target = document.createElement("div");
	target.className = 'noBorder';
	var d = new Date();
	var uniqueString = d.getTime();
	target.setAttribute('id',uniqueString);
	form.appendChild(target);
	var values = '';
	var url = "service/getAttributeValues.php";
	var params = "?type=defined";
	params += "&attribute_id="+attribute_id;
	Dase.createXMLHttpRequest(); 
	xmlhttp.open('GET', url+params, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var xml = xmlhttp.responseXML;
			var values = xml.getElementsByTagName('value');
			var list = document.createElement('ul');
			list.className = "inputList";
			if (values) {
				for (var i = 0; i < values.length; i++) {
					var list_item = document.createElement('li');
					var radio = document.createElement('input');
					var displayVal = values[i].firstChild.nodeValue;
					radio.setAttribute('type','radio');
					radio.setAttribute('value',displayVal);
					if (current == displayVal) {
						radio.setAttribute('checked','checked');
					}
					radio.setAttribute('name','new_val');
					var value_label = document.createElement("span");
					value_label.appendChild(document.createTextNode(displayVal));
					list_item.appendChild(radio);
					list_item.appendChild(value_label);
					list.appendChild(list_item);
				}
				var att = document.createElement("input");
				att.setAttribute("type","hidden");
				att.setAttribute("value",attribute_id);
				att.setAttribute("name","attribute_id");
				original.onclick = null;
				target.appendChild(att);
				target.appendChild(list);
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.getRadioButtons = function(attribute_id,target) {
	var values = '';
	var url = "service/getAttributeValues.php";
	var params = "?type=defined";
	params += "&attribute_id="+attribute_id;
	Dase.createXMLHttpRequest(); 
	xmlhttp.open('GET', url+params, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var xml = xmlhttp.responseXML;
			if (xml) {
				var values = xml.getElementsByTagName('value');
				var list = document.createElement('ul');
				list.className = "inputList";
				if (values) {
					for (var i = 0; i < values.length; i++) {
						var list_item = document.createElement('li');
						var radio = document.createElement('input');
						var displayVal = values[i].firstChild.nodeValue;
						radio.setAttribute('type','radio');
						radio.setAttribute('value',displayVal);
						radio.setAttribute('name','new_val');
						var value_label = document.createElement("span");
						value_label.appendChild(document.createTextNode(displayVal));
						list_item.appendChild(radio);
						list_item.appendChild(value_label);
						list.appendChild(list_item);
					}
					target.appendChild(list);
					var usageNotes = document.getElementById('usageNotes');
					if (usageNotes && xml.getElementsByTagName('usage')[0]) {
						var usageNode = xml.getElementsByTagName('usage')[0];
						usageNotes.appendChild(document.createTextNode('Usage Notes: '+usageNode.firstChild.nodeValue));
					}
				}
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.getCheckboxes = function(attribute_id,target) {
	var values = '';
	var url = "service/getAttributeValues.php";
	var params = "?type=defined";
	params += "&attribute_id="+attribute_id;
	Dase.createXMLHttpRequest(); 
	xmlhttp.open('GET', url+params, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			var xml = xmlhttp.responseXML;
			var values = xml.getElementsByTagName('value');
			var list = document.createElement('ul');
			list.className = "inputList";
			if (values) {
				for (var i = 0; i < values.length; i++) {
					var list_item = document.createElement('li');
					var check = document.createElement('input');
					var displayVal = values[i].firstChild.nodeValue;
					//apparently in ie6, order matters here!:
					//if in wrong order (type comes last) you just get 'on'
					//NOT the actual value
					check.setAttribute('type','checkbox');
					check.setAttribute('value',displayVal);
					check.setAttribute('name','new_val[]');
					var value_label = document.createElement("span");
					value_label.appendChild(document.createTextNode(displayVal));
					list_item.appendChild(check);
					list_item.appendChild(value_label);
					list.appendChild(list_item);
				}
				target.appendChild(list);
				var usageNotes = document.getElementById('usageNotes');
				if (usageNotes && xml.getElementsByTagName('usage')[0]) {
					var usageNode = xml.getElementsByTagName('usage')[0];
					usageNotes.appendChild(document.createTextNode('Usage Notes: '+usageNode.firstChild.nodeValue));
				}
			}
		} else {
			// wait for the call to complete
		}
	}
}

Dase.getViewportSize = function() {
	var size = [0, 0];
	if (typeof window.innerWidth != 'undefined') {
		size = [
			window.innerWidth,
			window.innerHeight
				];
	}
	else if (typeof document.documentElement != 'undefined'
			&& typeof document.documentElement.clientWidth != 'undefined'
			&& document.documentElement.clientWidth != 0) {
		size = [
			document.documentElement.clientWidth,
			document.documentElement.clientHeight
				];
	}
	else {
		size = [
			document.getElementsByTagName('body')[0].clientWidth,
			document.getElementsByTagName('body')[0].clientHeight
				];
	}
	return size;
}

Dase.getTextareaWidth = function() {
	var window_width = Dase.getViewportSize()[0];
	var srt = document.getElementById('itemTable');
	var image_width = srt.getElementsByTagName('img')[0].width;
	var textarea_width = Math.floor((window_width * .83)-(image_width)-120); 
	return textarea_width;
}

Dase.initResize = function() {
	window.onresize = function() {
		Dase.prepareEditable();
	};
	return true;
}

/* from DOM Scripting p. 103 */
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
}

addLoadEvent(function() {
		Dase.prepareAddFileUpload();
		Dase.prepareAttributeFlags();
		Dase.prepareCartAdds(); 
		Dase.prepareDeletable(); 
		Dase.prepareDeleteCommonValues();
		Dase.prepareDownload('download');
		Dase.prepareHelpModule();
		Dase.prepareHelpPopup();
		Dase.prepareLinkBack();
		Dase.prepareMediaLinks();
		Dase.prepareMine();
		Dase.prepareTagItems();
		Dase.prepareUploadValidation();
		Dase.setAutoReload();
		Dase.prepareRemoteLaunch('slideshowLaunch');
		Dase.initResize();
		Dase.prepareAddMetadata('addMetadata');
		Dase.prepareAddMetadata('addTagMetadata');
		Dase.prepareAddMetadata('uploadForm');
		Dase.prepareEditable();
		Dase.prepareGenericEditable();
		});

