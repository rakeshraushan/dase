Dase.pageInit = function() {
	if (Dase.$('browseColumns')) {
		var atts_link = Dase.$('collectionAtts');
		Dase.getAttributes(atts_link.href);
		var cats = Dase.$('catColumn').getElementsByTagName('a');
		for (var i=0;i<cats.length;i++) {
			var cat = cats[i];
			cat.onclick = function() {
				Dase.getAttributes(this.href);
				var cts = Dase.$('catColumn').getElementsByTagName('a');
				for (var j=0;j<cts.length;j++) {
					Dase.removeClass(cts[j],'spill');
				}
				this.className = 'spill';
				return false;
			};
		}
	}
};

Dase.getAttributes = function(url,sort) {
	var params;
	if (sort) {
		params = 'filter=public&sort='+sort;
	} else {
		params = 'filter=public';
	}
	Dase.getJSON(url,function(json) {
			Dase.$('attColumn').innerHTML = Dase.processAtts(json);
			Dase.getAttributeTallies(url.replace(/attributes/,'attribute_tallies'));
			Dase.bindGetValues(Dase.$('collectionAsciiId').innerHTML);
			Dase.initAttSort();
			},null,params);
	var val_coll = Dase.$('valColumn');
	val_coll.className = 'hide';
};

Dase.processAtts = function(json) {
	h = new Dase.htmlbuilder('div');
	h.add('a',{'href':'#','id':'attSorter'},'toggle sort');
	h.add('h4',null,'Select Attribute:');
	var ul = h.add('ul',{'id':'attList'});
	for (var i=0;i<json.length;i++) {
		var att = json[i];


		var li = ul.add('li');
		var a = li.add('a');
		a.set('href','attribute/'+att.collection+'/'+att.ascii_id+'/values.json');
		a.set('id',att.ascii_id);
		a.set('class','att_link '+att.sort_order);
		a.add('span',{'class':'att_name'},att.attribute_name);
		/* NOTE: adding &nbsp; was necessary for IE7 here */
		a.add('span',{'class':'tally','id':'tally-'+att.ascii_id},'&nbsp;');
	}
	return h.getString();
}

Dase.processVals = function(json) {
	h = new Dase.htmlbuilder('div');
	h.add('h4',null,'Select '+json.att_name+' Value:');
	var ul = h.add('ul',{'id':'valList'});
	for (var i=0;i<json.values.length;i++) {
		var val = json.values[i];
		var li = ul.add('li');
		var a = li.add('a');
		a.set('href','search?c='+json.coll+'&amp;q='+json.att_ascii+':&quot;'+encodeURIComponent(val.v)+'&quot;');
		a.set('class','val_link');
		a.add('span',null,val.v);
		a.add('span',{'class':'tally'},'('+val.t+')');
	}
	return h.getString();
}

Dase.initAttSort = function() {
	link = Dase.$('attSorter');
	link.onclick = function() {
		if (Dase.attsort) {
			Dase.getAttributes(Dase.$('collectionAtts').href);
			Dase.attsort = 0;
			return false;
		} else {
			Dase.getAttributes(Dase.$('collectionAtts').href,'attribute_name');
			Dase.attsort = 1;
			return false;
		}
	}
}

Dase.bindGetValues = function(coll) {
	var atts = Dase.$('attColumn').getElementsByTagName('a');
	for (var i=0;i<atts.length;i++) {
		var att_link = atts[i];
		if (Dase.hasClass(att_link,'att_link')) {
			att_link.onclick = function() {
				var att_name = this.getElementsByTagName('span')[0].innerHTML;
				var att_ascii = this.className.split(" ")[1];
				Dase.getAttributeValues(this.href);	
				Dase.removeClass(Dase.$('valColumn'),'hide');
				window.scroll(0,0);
				for (var j=0;j<atts.length;j++) {
					Dase.removeClass(atts[j],'spill');
				}
				Dase.addClass(this,'spill');
				return false;
			};
		}
	}
};

Dase.getAttributeValues = function(url) {
	Dase.$('valColumn').innerHTML = 'loading...';
	Dase.getJSON(url,function(json) {
			Dase.$('valColumn').innerHTML = Dase.processVals(json);
			});
};

Dase.getAttributeTallies = function(url) {
	Dase.getJSON(url,function(json) {
			for(var ascii_id in json.tallies) {
			var	att_link = Dase.$(ascii_id);
			if (att_link) {
			var tally = Dase.$('tally-'+ascii_id);
			if (tally) {
			if (0 == json.tallies[ascii_id]) {
			tally.innerHTML = '(0)';
			//make admin atts w/ no values disappear
			if (json.is_admin) {
			tally.parentNode.className = 'hide';
			}
			} else {
			tally.innerHTML = '(' + json.tallies[ascii_id] + ')';
			}
			} } }
			Dase.loadingMsg(false);
	});
};
