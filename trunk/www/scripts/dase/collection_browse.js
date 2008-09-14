Dase.pageInit = function() {
	if (Dase.$('browseColumns')) {
		var att_coll = Dase.$('attColumn');
		Dase.getAttributes(att_coll.className);
		att_coll.className = '';
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

Dase.getAttributes = function(url) {
	Dase.getJSON(url,function(json) {
			var data = { 'atts': json };
			var templateObj = TrimPath.parseDOMTemplate("atts_jst");
			Dase.$('attColumn').innerHTML = templateObj.process(data);
			Dase.getAttributeTallies(url+'/tallies');
			Dase.bindGetValues(Dase.$('collectionAsciiId').innerHTML);
			Dase.initAttSort();
			});
	var val_coll = Dase.$('valColumn');
	val_coll.className = 'hide';
};

Dase.attItemCompareAlpha = function(a,b) {
	aname = a.getElementsByTagName('span')[0].innerHTML;
	bname = b.getElementsByTagName('span')[0].innerHTML;
	if (aname < bname) {
		return -1;
	}
	if (aname > bname) {
		return 1;
	}
	if (aname == bname) {
		return 0;
	}
}

Dase.initAttSort = function() {
	link = Dase.$('attSorter');
	link.onclick = function() {
		if ('unsort' == this.innerHTML) {
			Dase.getAttributes(Dase.$('collectionAtts').href);
			return false;
		}
		this.innerHTML = 'unsort';
		list = Dase.$('attList');
		items = list.getElementsByTagName('li');
		att_array = []
		for (var i=0;i<items.length; i++) {
			att_array[att_array.length] = items[i]
		}
		list.innerHTML = 'sorting...';
		att_array.sort(Dase.attItemCompareAlpha);
		list.innerHTML = '';
		for (var i=0;i<att_array.length; i++) {
			list.innerHTML += '<li>'+att_array[i].innerHTML+'</li>';
		}
		Dase.bindGetValues(Dase.$('collectionAsciiId').innerHTML);
		Dase.initAttSort();
		return false;
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
			var templateObj = TrimPath.parseDOMTemplate("vals_jst");
			Dase.$('valColumn').innerHTML = templateObj.process(json);
			//encodeURIComponent(text)
			Dase.getAttributeTallies(url+'/tallies');
			Dase.bindGetValues(Dase.$('collectionAsciiId').innerHTML);
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
