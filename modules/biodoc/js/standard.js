function ShowDiv(divid) {
	if (document.layers) document.layers[divid].visibility="show";
	else document.getElementById(divid).style.visibility="visible";
}   

function HideDiv(divid) {
	if (document.layers) document.layers[divid].visibility="hide";
	else document.getElementById(divid).style.visibility="hidden";
}

function confirmDelete(theElement) {
	var agree=confirm('Think carefully...you are about to delete\n' + theElement);
	if (agree) {
		return true;
	}
	return agree;
}

function confirmCopyright() {
	var statement='Material on this site may be used for educational purposes by current faculty, students, and staff of the University of Texas at Austin. This site provides access to materials, licensed or otherwise, for which the copyright is held by owners other than the University of Texas at Austin. Use of these materials and resources is restricted by applicable license agreement and copyright law.';
	var agree=confirm(statement);
	if (agree) {
		return true;
	}
	return agree;
}

function toggleName(name){
	nameElements = document.getElementsByName(name);
	for (i = 0; i< nameElements.length; i++) {
		if (nameElements[i].className == 'closed'){
			nameElements[i].className = 'open';
		}else{
			nameElements[i].className = 'closed';
		}
	}
}

function toggle(id){
	menu = id;
	menuElement = document.getElementById(menu);
	if (menuElement){
		if (menuElement.className == 'closed'){
			menuElement.className = "item";
		}else{
			menuElement.className = "closed";
		}
	}       
}       

function helpwin(URL)
{
	/*confirmCopyright();*/
	window_handle = window.open(URL,'help','toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=290,height=600'); window_handle.focus(); return true; 
}

function openwin(URL,height,width)
{
	/*confirmCopyright();*/
	var w = width+33;
	var h = height+33;
	window_handle = window.open(URL,'','toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=' + w + ',height=' + h); window_handle.focus(); return true; 
}
/************* dase_ajax.js *****************************/


var xmlhttp = null;

function getHtml(url,elem_id,my_func) {
	document.getElementById(elem_id).innerHTML = '<div class="loading">Loading...</div>';
	createXMLHttpRequest(); //had to put constructor here so key-up functions work
	xmlhttp.open('GET', url, true);
	xmlhttp.send(null);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			document.getElementById(elem_id).innerHTML = xmlhttp.responseText;
			if (my_func) {
				my_func();
			}
		} else {
			// wait for the call to complete
		}
	}
}

function clearHtml(elem_id) {
	document.getElementById(elem_id).innerHTML = '';
}

function createXMLHttpRequest() {
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		alert('Perhaps your browser does not support xmlhttprequests?');
	}
}

/* from DOM Scripting p. 103 */
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			oldonload();
			func;
		}
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

function getAttributes() {
	var url = 'service/getAttributes.php';
	getHtml(url,'att_column',bindGetValues);
	clearHtml('val_column');
}

function nullFunc() {
}

function bindGetValues() {
	var att_list=document.getElementById("att_column");
	var att_links=att_list.getElementsByTagName('a');
	if (att_links) {
		for (var i=0;i<att_links.length;i++) {
			var att_link=att_links[i];
			att_link.onclick=getValues;
		}
	}
}

function getKeywords() {
	var url = 'service/getKeywords.php';
	getHtml(url,'keywords_target',prepareKwSelect);
	return false;
}

function prepareKwSelect() {
	var kwform = document.getElementById('kwform');
	var kw_select = document.getElementById('kw_select');
	kw_select.onchange = function() {
		kwform.submit();
	}
}



function getValues(event) {
	var id=this.id;
	var url = 'service/getValuesByAtt.php';
	var par = '?attribute_id=' +id; 
	var spill_elem_id = id;
	var att_links = getElementsByClassName(document.getElementById('att_column'),'a','spill');
	for (var i=0; i<att_links.length; i++) {
		att_links[i].className =  'att_link';
	}
	document.getElementById(spill_elem_id).className = 'spill';
	getHtml(url+par,'val_column');
	return false;
}

function getTypeAhead(query,collection_id) {
	var url = 'service/getTypeAhead.php';
	var par = '?collection_id=' + collection_id + '&query_str=' +query; 
	getHtml(url+par,'autocomplete');
}

function indicateStatus() {
	document.getElementById('status').innerHTML = 'searching the archive...';
}

function bindStatus() {
	document.getElementById('searchArchiveForm').onsubmit = indicateStatus;
}

/************* laits_ajax.js ***************/


function toggleTree(li_node){
	var id = li_node.getAttribute('id');
	var sub = id + '-sub';
	var liElement = document.getElementById(id);
	if (liElement){
		if (liElement.className == 'shut'){
			liElement.className = 'open';
		}else{
			liElement.className = 'shut';
		}
	}       
	var subElement = document.getElementById(sub);
	if (subElement){
		if (subElement.className == 'open'){
			subElement.className = 'closed';
		}else{
			subElement.className = 'open';
		}
	}       
	return false;
}       

function displayDebug (debug) {
	var footer = document.getElementById('debug');
	var h3 = document.createElement('h3');
	h3.appendChild(document.createTextNode(debug));
	footer.appendChild(h3);
}


function prepareUnitForm() {
        var select = document.getElementById('unitFormSelect');
        var form = document.getElementById('unitForm');
        select.onchange = function() {
                var topic_sel = document.getElementById('topicSelect');
                topic_sel.className = 'hide';
                var loading_notice = document.getElementById('loading');
                loading_notice.innerHTML = 'loading topics...';
                var rm = document.getElementById('runMode');
                rm.value='getTopicsByUnit';
                form.submit();
        }

}


addLoadEvent(function() { 
	prepareUnitForm();
});
