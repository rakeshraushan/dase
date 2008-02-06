Dase.initCollection = function() {
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
};

Dase.getCollectionData = function(select,my_func) {
	//select could be any of:
	//attributes,types,settings,managers,all
	Dase.loadingMsg(true);
	var coll = Dase.$('collection_ascii_id').innerHTML;
	Dase.getJSON(Dase.base_href + "json/collection/" + coll + "/data/"+select,function(json){
			var target = Dase.$('collectionData');
			while (target.childNodes[0]) {
			target.removeChild(target.childNodes[0]);
			}
			switch(select) {
			case 'attributes':
			//Dase.createListFromObj(target,json,'collData');
			Dase.$('pageHeader').innerHTML = "Attribute Settings";
			Dase.buildAttributeTable(target,json.attributes,coll);
			if (my_func) {
			my_func();
			}
			break;
			case 'types':
			Dase.$('pageHeader').innerHTML = "Item Types";
			break;
			case 'settings':
			Dase.$('pageHeader').innerHTML = "Administrative Settings";
			Dase.buildSettingsTable(target,json.settings,coll);
			break;
			case 'managers':
			Dase.$('pageHeader').innerHTML = "Collection Managers";
			Dase.buildManagersTable(target,json.managers,coll);
			break;
			}
	});
};

Dase.buildAttributeTable = function(parent,atts,c_ascii) {
	//note that atts need ot be an array so I can 
	//iterate over them and maintain the sort
	var inputTypes = Dase.htmlInputTypeLabel;
	parent.className = 'show';
	var tbl = document.createElement('table');
	tbl.setAttribute('class','dataDisplay');
	parent.appendChild(tbl);
	var headers = document.createElement('tr');
	tbl.appendChild(headers);
	var labels = ["","Order","Input Type","In Basic Search","On List Display","Is Public","Usage Note"];
	Dase.createHtmlSet(headers,labels,'th');
	for (var i = 0;i<atts.length;i++) {
		var attrow = document.createElement('tr');
		attrow.setAttribute('id',atts[i].ascii_id+'_row');
		var attlabel = document.createElement('th');
		attlabel.setAttribute('class','rows');
		var attlabelLink = document.createElement('a');
		//need to put collection name in href!!
		attlabelLink.setAttribute('href',atts[i].ascii_id);
		attlabelLink.appendChild(document.createTextNode(atts[i].attribute_name));
		attlabel.appendChild(attlabelLink);
		attlabelLink.onclick = function() {
			var attrowEditRow = document.createElement('tr');
			var attrowEditHeaderCell = document.createElement('th');
			attrowEditHeaderCell.setAttribute('class','rows');
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
		var so = Dase.createElem(attrow,'','td');
		var up = Dase.createElem(so,'','a');
		var u_arrow = Dase.createElem(up,'','img');
		u_arrow.setAttribute('src','images/down.png');
		up.setAttribute('href','admin/'+Dase.user.eid+'/'+c_ascii+'/attribute/'+atts[i].ascii_id+'/sort_order');
		up.setAttribute('class','clickTarget');
		up.new_sort_order = i+2;
		up.row_id = atts[i].ascii_id+'_row';
		up.onclick = function() {
			var row_id = this.row_id;
			Dase.highlight(row_id,3000);
			Dase.ajax(this.href,'put',function() { 
					Dase.getCollectionData('attributes',function() { Dase.highlight(row_id,1000); });
					},this.new_sort_order);
			return false;
		};
		so.appendChild(document.createTextNode('/'));
		var down = Dase.createElem(so,'','a');
		var d_arrow = Dase.createElem(down,'','img');
		d_arrow.setAttribute('src','images/up.png');
		down.setAttribute('href','admin/'+Dase.user.eid+'/'+c_ascii+'/attribute/'+atts[i].ascii_id+'/sort_order');
		down.setAttribute('class','clickTarget');
		down.new_sort_order = i;
		down.row_id = atts[i].ascii_id+'_row';
		down.onclick = function() {
			var row_id = this.row_id;
			Dase.highlight(row_id,3000);
			Dase.ajax(this.href,'put',function() { 
					Dase.getCollectionData('attributes',function() { Dase.highlight(row_id,1000); });
					},this.new_sort_order);
			return false;
		};
		Dase.createElem(attrow,inputTypes[atts[i].html_input_type_id],'td');
		var basic = Dase.createElem(attrow,'','td');
		Dase.createCheckbox(basic,atts[i].in_basic_search,'basic_search');
		var onList = Dase.createElem(attrow,'','td');
		Dase.createCheckbox(onList,atts[i].is_on_list_display,'on_list');
		var pub = Dase.createElem(attrow,'','td');
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
}

Dase.buildManagersTable = function(parent,managers,c_ascii) {
	//note that atts need ot be an array so I can 
	//iterate over them and maintain the sort
	var inputTypes = Dase.htmlInputTypeLabel;
	parent.className = 'show';
	var tbl = document.createElement('table');
	tbl.setAttribute('class','dataDisplay');
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
				td = Dase.createElem(tr,'','td');
				td.appendChild(document.createTextNode(m.auth_level));
				tr.appendChild(td);
				td = Dase.createElem(tr,'','td');
				td.appendChild(document.createTextNode(m.created));
				tr.appendChild(td);
				td = Dase.createElem(tr,'','td');
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
}

Dase.buildSettingsTable = function(parent,settings,c_ascii) {
	var inputTypes = Dase.htmlInputTypeLabel;
	parent.className = 'show';
	var tbl = document.createElement('table');
	tbl.setAttribute('class','dataDisplay');
	parent.appendChild(tbl);

	var tr = document.createElement('tr');
	tr.setAttribute('id','collection_name_row');
	var th = document.createElement('th');
	th.setAttribute('class','rows');
	th.appendChild(document.createTextNode('Collection Name'));
	tr.appendChild(th);
	var td = document.createElement('td');
	td.setAttribute('class','rows');
	td.appendChild(document.createTextNode(settings.collection_name));
	tr.appendChild(td);
	tbl.appendChild(tr);

	tr = document.createElement('tr');
	tr.setAttribute('id','created_row');
	th = document.createElement('th');
	th.setAttribute('class','rows');
	th.appendChild(document.createTextNode('Created'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.setAttribute('class','rows');
	td.appendChild(document.createTextNode(settings.created));
	tr.appendChild(td);
	tbl.appendChild(tr);

	tr = document.createElement('tr');
	tr.setAttribute('id','is_public_row');
	th = document.createElement('th');
	th.setAttribute('class','rows');
	th.appendChild(document.createTextNode('Is Public?'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.setAttribute('class','rows');
	td.appendChild(document.createTextNode(settings.is_public));
	tr.appendChild(td);
	tbl.appendChild(tr);

	tr = document.createElement('tr');
	tr.setAttribute('id','description_row');
	th = document.createElement('th');
	th.setAttribute('class','rows');
	th.appendChild(document.createTextNode('Description'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.setAttribute('class','rows');
	td.appendChild(document.createTextNode(settings.description));
	tr.appendChild(td);
	tbl.appendChild(tr);

	tr = document.createElement('tr');
	tr.setAttribute('id','path_to_media_files_row');
	th = document.createElement('th');
	th.setAttribute('class','rows');
	th.appendChild(document.createTextNode('Path To Media'));
	tr.appendChild(th);
	td = document.createElement('td');
	td.setAttribute('class','rows');
	td.appendChild(document.createTextNode(settings.path_to_media_files));
	tr.appendChild(td);
	tbl.appendChild(tr);
}







Dase.addLoadEvent(function() {
		Dase.initCollection();
		});

