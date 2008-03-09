Dase.initCollection = function() {
	Dase.$('collUpload').onclick = function() {
		Dase.displayUploadForm();
		return false;
	}
	Dase.$('collAttributes').onclick = function() {
		Dase.getCollectionData('attributes');
		return false;
	}
	Dase.$('collTypes').onclick = function() {
		Dase.getCollectionData('types');
		return false;
	}
	Dase.$('collSettings').onclick = function() {
		Dase.getCollectionData('settings');
		return false;
	}
	Dase.$('collManagers').onclick = function() {
		Dase.getCollectionData('managers');
		return false;
	}
	//default view:
	Dase.getCollectionData('settings');
};

Dase.displayUploadForm = function() {
	Dase.loadingMsg(true);
	var coll = Dase.$('collection_ascii_id').innerHTML;
	var coll_name = Dase.$('collection_name').innerHTML;
	Dase.$('pageHeader').innerHTML = coll_name+" Upload Form";
	var target = Dase.$('collectionData');
	while (target.childNodes[0]) {
		target.removeChild(target.childNodes[0]);
	}
	target.className = 'show';
	var div = document.createElement('div');
	div.setAttribute('class','adminForm');
	div.setAttribute('className','adminForm');
	target.appendChild(div);
	Dase.createElem(div,'upload form','h2');
};

Dase.getCollectionData = function(select,my_func) {
	//select could be any of:
	//attributes,types,settings,managers,all
	Dase.loadingMsg(true);
	var coll = Dase.$('collection_ascii_id').innerHTML;
	var coll_name = Dase.$('collection_name').innerHTML;
	Dase.getJSON(Dase.base_href + "json/collection/" + coll + "/data/"+select,function(json){
			var target = Dase.$('collectionData');
			var doomed = Dase.$('dataDisplay');
			while (doomed.childNodes[0]) {
			doomed.removeChild(doomed.childNodes[0]);
			}
			switch(select) {
			case 'attributes':
			//Dase.createListFromObj(target,json.attributes,'collData');
			Dase.$('pageHeader').innerHTML = coll_name+" Attribute Settings";
			Dase.buildAttributeTable(target,json.attributes,coll);
			if (my_func) {
			my_func();
			}
			break;
			case 'types':
			Dase.$('pageHeader').innerHTML = coll_name+" Item Types";
			Dase.buildTypesTable(target,json.item_types,coll);
			//Dase.createListFromObj(Dase.$('debugData'),json.item_types,'show');
			break;
			case 'settings':
			Dase.$('pageHeader').innerHTML = coll_name+" Administrative Settings";
			Dase.buildSettingsTable(target,json.settings,coll);
			//Dase.buildDebugTable(target,json.managers,coll);
			break;
			case 'managers':
			Dase.$('pageHeader').innerHTML = coll_name+" Collection Managers";
			Dase.buildManagersTable(target,json.managers,coll);
			break;
			}
	});
};

Dase.buildTable = function(parent,atts,c_ascii) {
	//alert(JSON.stringify(atts));
	var labels = [
		"Attribute Name",
		"HTML Input Type",
		"In Basic Search",
		"Is On List Display",
		"Is Public",
		"Mapped Admin Attribute",
		"Sort Order",
		"Updated",
		"Usage Notes"
	]
	var cols = [
		 "attribute_name",
		 "html_input_type_id",
		 "in_basic_search",
		 "is_on_list_display",
		 "is_public",
		 "mapped_admin_att_id",
		 "sort_order",
		 "updated",
		 "usage_notes",
	]
	var thead = table.createTHead();
	var cell;
	var row = thead.insertRow(-1);
	var i,j;

	for (i=0; i<labels.length; i++) {
		cell = row.insertCell(-1);
		cell.innerHTML = labels[i];
	}

	for (i=0; i<atts.length; i++) {
		row = table.insertRow(-1);
		for (j=0;j<cols.length;j++) {
			cell = row.insertCell(-1);
			cell.innerHTML = atts[i][cols[j]];
		}
	}
}

Dase.buildAttributeTable = function(parent,atts,c_ascii) {
	//alert(JSON.stringify(atts));
	parent.className = 'show';
	var table = Dase.$('dataDisplay');
	/*
	for (i=0; i<atts.length; i++) {
		row = table.insertRow(-1);
		for (j=0;j<cols.length;j++) {
			cell = row.insertCell(-1);
			cell.innerHTML = atts[i][cols[j]];
		}
	}
	*/
	//note that atts need ot be an array so I can 
	//iterate over them and maintain the sort
	var inputTypes = Dase.htmlInputTypeLabel;
	parent.className = 'show';
	var table = Dase.$('dataDisplay');
	var thead = table.createTHead();
	var row = thead.insertRow(-1);
	var labels = [
		"Ascii Id",
		"Attribute Name",
		"Collection Id",
		"HTML Input Type",
		"Id",
		"In Basic Search",
		"Is On List Display",
		"Is Public",
		"Mapped Admin Attribute",
		"Sort Order",
		"Updated",
		"Usage Notes"
	]
	var cols = [
	{"term":"ascii_id","label":"Ascii Id","fx":function() {alert('ss');}},
	{"term":"attribute_name","label":"Attribute Name","fx":function() {alert('ss'); }},
	{"term":"collection_id","label":"Collection Id","fx":function() {alert('ss'); }},
	{"term":"html_input_type_id","label":"HTML Input Type","fx":function() {alert('ss'); }},
	{"term":"id","label":"Id","fx":function() {alert('ss'); }},
	{"term":"in_basic_search","label":"In Basic Search","fx":function() {alert('ss'); }},
	{"term":"is_on_list_display","label":"Is On List Display","fx":function() {alert('ss'); }},
	{"term":"is_public","label":"Is Public","fx":function() { alert('ss');}},
	{"term":"mapped_admin_att_id","label":"Mapped Admin Att","fx":function() { alert('ss');}},
	{"term":"sort_order","label":"Sort Order","fx":function() { alert('ss');}},
	{"term":"updated","label":"Updated","fx":function() { alert('ss');}},
	{"term":"usage_notes","label":"Usage Notes","fx":function() { alert('ss');}}
	]

	var cell;
	for (i=0; i<cols.length; i++) {
		cell = row.insertCell(-1);
		var attlabelLink = document.createElement('a');
		//need to put collection name in href!!
		attlabelLink.setAttribute('href',cols[i].term);
		attlabelLink.appendChild(document.createTextNode(cols[i].label));
		cell.appendChild(attlabelLink);
		attlabelLink.onclick = cols[i].fx; 
	}

	for (var i = 0;i<atts.length;i++) {
		var attrow = document.createElement('tr');
		attrow.setAttribute('id',atts[i].ascii_id+'_row');
		var attlabel = document.createElement('th');
		attlabel.className = 'rows';
		var attlabelLink = document.createElement('a');
		//need to put collection name in href!!
		attlabelLink.setAttribute('href',atts[i].ascii_id);
		attlabelLink.appendChild(document.createTextNode(atts[i].attribute_name));
		attlabel.appendChild(attlabelLink);
		attlabelLink.onclick = function() {
			var attrowEditRow = document.createElement('tr');
			var attrowEditHeaderCell = document.createElement('th');
			attrowEditHeaderCell.setAttribute('class','rows');
			attrowEditHeaderCell.setAttribute('className','rows');
			attrowEditRow.appendChild(attrowEditHeaderCell);
			var attrowEditCell = document.createElement('td');
			attrowEditRow.appendChild(attrowEditCell);
			attrowEditCell.setAttribute('colspan','6');
			attrowEditCell.appendChild(document.createTextNode('EDIT dfrg fdgst rt htw wr wh   HEEERE'));
			var thisrow = this.parentNode.parentNode;
			var nextrow = thisrow.nextSibling;
			tbl.insertBefore(attrowEditRow,nextrow);
			this.onclick = function() {
				Dase.getCollectionData('attributes');
				return false;
			};
			return false;
		};
		attrow.appendChild(attlabel);
		var so = Dase.createElem(attrow,null,'td');
		var down = Dase.createElem(so,null,'a');
		var d_arrow = Dase.createElem(down,null,'img');
		d_arrow.setAttribute('src','images/up.png');
		down.setAttribute('href','admin/'+Dase.user.eid+'/'+c_ascii+'/attribute/'+atts[i].ascii_id+'/sort_order');
		down.setAttribute('class','clickTarget');
		down.setAttribute('className','clickTarget');
		down.new_sort_order = i;
		down.row_id = atts[i].ascii_id+'_row';
		down.onclick = function() {
			var row = Dase.$(this.row_id);
			Dase.highlight(row,3000);
			Dase.ajax(this.href,'put',function() { 
					Dase.getCollectionData('attributes',function() { Dase.highlight(row,1000); });
					},this.new_sort_order);
			return false;
		};
		var up = Dase.createElem(so,null,'a');
		var u_arrow = Dase.createElem(up,null,'img');
		u_arrow.setAttribute('src','images/down.png');
		up.setAttribute('href','admin/'+Dase.user.eid+'/'+c_ascii+'/attribute/'+atts[i].ascii_id+'/sort_order');
		up.setAttribute('class','clickTarget');
		up.new_sort_order = i+2;
		up.row_id = atts[i].ascii_id+'_row';
		up.onclick = function() {
			var row = Dase.$(this.row_id);
			Dase.highlight(row,3000);
			Dase.ajax(this.href,'put',function() { 
					Dase.getCollectionData('attributes',function() { Dase.highlight(row,1000); });
					},this.new_sort_order);
			return false;
		};
		Dase.createElem(attrow,inputTypes[atts[i].html_input_type_id],'td');
		var basic = Dase.createElem(attrow,null,'td');
		Dase.createCheckbox(basic,atts[i].in_basic_search,'basic_search');
		var onList = Dase.createElem(attrow,null,'td');
		Dase.createCheckbox(onList,atts[i].is_on_list_display,'on_list');
		var pub = Dase.createElem(attrow,null,'td');
		Dase.createCheckbox(pub,atts[i].is_public,'public');
		Dase.createElem(attrow,atts[i].usage_notes,'td');
		tbl.appendChild(attrow);
	}
	/*
	 * ascii_id
	 * attribute_name
	 * collection_id
	 * html_input_type_id
	 * in_basic_search
	 * is_on_list_display
	 * is_public
	 * mapped_admin_att_id
	 * sort_order
	 * updated
	 * usage_notes 
	 */
};

Dase.buildDebugTable = function(parent,settings,c_ascii) {
	//note that atts need ot be an array so I can 
	//iterate over them and maintain the sort
	parent.className = 'show';
	var tbl = document.createElement('table');
	tbl.setAttribute('class','dataDisplay');
	tbl.setAttribute('className','dataDisplay');
	parent.appendChild(tbl);
	var headers = document.createElement('tr');
	tbl.appendChild(headers);
	var labels = ["","Auth Level","Created","Expires"];
	Dase.createHtmlSet(headers,labels,'th');
	for (var i = 0;i<settings.length;i++) {
		var m = settings[i];
		alert(m);
	}
};

Dase.buildManagersTable = function(parent,managers,c_ascii) {
	//note that atts need ot be an array so I can 
	//iterate over them and maintain the sort
	parent.className = 'show';
	var tbl = document.createElement('table');
	tbl.setAttribute('class','dataDisplay');
	tbl.setAttribute('className','dataDisplay');
	parent.appendChild(tbl);
	var headers = document.createElement('tr');
	tbl.appendChild(headers);
	var labels = ["","Auth Level","Created","Expires"];
	Dase.createHtmlSet(headers,labels,'th');
	for (var i = 0;i<managers.length;i++) {
		var m = managers[i];
		var tr = document.createElement('tr');
		tr.setAttribute('id',m.dase_user_eid+'_row');
		var th = document.createElement('th');
		th.setAttribute('class','rows');
		th.setAttribute('className','rows');
		var a = document.createElement('a');
		a.setAttribute('href',m.dase_user_eid);
		a.appendChild(document.createTextNode(m.name+' ('+m.dase_user_eid+')'));
				th.appendChild(a);
				a.onclick = function() {
				var tr = document.createElement('tr');
				var td = document.createElement('td');
				tr.appendChild(td);
				td.setAttribute('colspan','4');
				td.appendChild(document.createTextNode('EDIT dfrg fdgst rt htw wr wh   HEEERE'));
				var thisrow = this.parentNode.parentNode;
				var nextr = thisrow.nextSibling;
				tbl.insertBefore(tr,nextr);
				this.onclick = function() {
				Dase.getCollectionData('managers');
				return false;
				};
				return false;
				};
				tr.appendChild(th);
				td = Dase.createElem(tr,null,'td');
				td.appendChild(document.createTextNode(m.auth_level));
				tr.appendChild(td);
				td = Dase.createElem(tr,null,'td');
				td.appendChild(document.createTextNode(m.created));
				tr.appendChild(td);
				td = Dase.createElem(tr,null,'td');
				td.appendChild(document.createTextNode(m.expiration));
				tr.appendChild(td);
				tbl.appendChild(tr);
	}
	/*
	 * auth_level superuser
	 * collection_ascii_id test
	 * created
	 * dase_user_eid welchle2
	 * expiration 
	 */
};

Dase.createTable = function(oTable,heading,data)
{
	//NOTE that the required tbody element
	//is implicitly created 
	var oTHead = oTable.createTHead();
	var oCell;
	var oRow = oTHead.insertRow(-1);

	for (i=0; i<heading.length; i++) {
		oCell = oRow.insertCell(-1);
		oCell.innerHTML = heading[i];
	}

	for (var i=0; i<data.length; i++) {
		oRow = oTable.insertRow(-1);
		for (j=0; j<data[i].length; j++)
		{
			oCell = oRow.insertCell(-1);
			oCell.innerHTML = data[i][j];
		}
	}
};


Dase.buildSettingsTable = function(parent,settings,c_ascii) {
	//alert(JSON.stringify(settings));
	parent.className = 'show';
	var table = Dase.$('dataDisplay');
	var tbl = table.getElementsByTagName('tbody')[0];

	var tr = document.createElement('tr');
	tr.setAttribute('id','collection_name_row');
	var th = document.createElement('th');
	th.className='rows';
	th.appendChild(document.createTextNode('Collection Name'));
	tr.appendChild(th);
	var td = document.createElement('td');
	td.className='rows';
	td.appendChild(document.createTextNode(settings.collection_name));
	tr.appendChild(td);
	tbl.appendChild(tr);


	tr = document.createElement('tr');
	tr.setAttribute('id','created_row');
	th = document.createElement('th');
	th.className='rows';
	th.appendChild(document.createTextNode('Created'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.className='rows';
	td.appendChild(document.createTextNode(settings.created));
	tr.appendChild(td);
	tbl.appendChild(tr);

	tr = document.createElement('tr');
	tr.setAttribute('id','is_public_row');
	th = document.createElement('th');
	th.className='rows';
	th.appendChild(document.createTextNode('Is Public?'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.className='rows';
	td.appendChild(document.createTextNode(settings.is_public));
	tr.appendChild(td);
	tbl.appendChild(tr);

	tr = document.createElement('tr');
	tr.setAttribute('id','description_row');
	th = document.createElement('th');
	th.className='rows';
	th.appendChild(document.createTextNode('Description'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.className='rows';
	td.appendChild(document.createTextNode(settings.description));
	tr.appendChild(td);
	tbl.appendChild(tr);

	tr = document.createElement('tr');
	tr.setAttribute('id','path_to_media_files_row');
	th = document.createElement('th');
	th.className='rows';
	th.appendChild(document.createTextNode('Path To Media'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.className='rows';
	td.appendChild(document.createTextNode(settings.path_to_media_files));
	tr.appendChild(td);
	tbl.appendChild(tr);
};

Dase.buildTypesTable = function(parent,types,c_ascii) {
	parent.className = 'show';
	var tbl = document.createElement('table');
	tbl.setAttribute('class','dataDisplay');
	tbl.setAttribute('className','dataDisplay');
	parent.appendChild(tbl);
	var headers = document.createElement('tr');
	tbl.appendChild(headers);
	var labels = ["Name","Ascii Id","Description","Attributes"];
	Dase.createHtmlSet(headers,labels,'th');
	for (var ascii_id in types) {
		var typerow = document.createElement('tr');
		typerow.setAttribute('id',types[ascii_id].ascii_id+'_row');
		var typelabel = document.createElement('th');
		typelabel.setAttribute('class','rows');
		typelabel.setAttribute('className','rows');
		var typelabelLink = document.createElement('a');
		typelabelLink.setAttribute('href',types[ascii_id].ascii_id);
		typelabelLink.appendChild(document.createTextNode(types[ascii_id].name));
		typelabel.appendChild(typelabelLink);
		typelabelLink.onclick = function() {
			var typerowEditRow = document.createElement('tr');
			var typerowEditHeaderCell = document.createElement('th');
			typerowEditHeaderCell.setAttribute('class','rows');
			typerowEditHeaderCell.setAttribute('className','rows');
			typerowEditRow.appendChild(typerowEditHeaderCell);
			var typerowEditCell = document.createElement('td');
			typerowEditRow.appendChild(typerowEditCell);
			typerowEditCell.setAttribute('colspan','6');
			typerowEditCell.appendChild(document.createTextNode('EDIT dfrg fdgst rt htw wr wh   HEEERE'));
			var thisrow = this.parentNode.parentNode;
			var nextrow = thisrow.nextSibling;
			tbl.insertBefore(typerowEditRow,nextrow);
			this.onclick = function() {
				Dase.getCollectionData('types');
				return false;
			};
			return false;
		};
		Dase.createElem(typerow,types[ascii_id].name,'th');
		Dase.createElem(typerow,types[ascii_id].ascii_id,'td');
		Dase.createElem(typerow,types[ascii_id].description,'td');
		var attsCell = Dase.createElem(typerow,'','td','data');
		//if an item type has attributes, make a list of 'em
		if (types[ascii_id].attributes) {
			var attList = Dase.createElem(attsCell,'','dl');
			for (var i=0;i<types[ascii_id].attributes.length;i++) {
				myAtt = types[ascii_id].attributes[i];
				Dase.createElem(attList,myAtt.attribute_name,'dt');
				Dase.createElem(attList,myAtt.cardinality,'dd');
				Dase.createElem(attList,myAtt.is_identifier,'dd');
			}
		}
		tbl.appendChild(typerow);
	}
	/*
	 * ascii_id
	 * name
	 * collection_id
	 * description
	 * attributes
	 */
};


Dase.addLoadEvent(function() {
		Dase.initCollection();
		});

