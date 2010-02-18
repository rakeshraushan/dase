//item editing needs to know user, 
//so we use the 'pageInitUser' function
Dase.pageInitUser = function(eid) {
	Dase.recordSearchView(eid);
};

Dase.recordSearchView = function(eid) {
	pageurl = 'http://'+encodeURIComponent(location.href.replace('http://',''));
	title = encodeURIComponent(Dase.getMeta('item-title'));
	var content_headers = {
		'Content-Type':'application/x-www-form-urlencoded'
	}
	var url = Dase.base_href+'user/'+Dase.user.eid+'/recent_searches';
	var count = Dase.getMeta('item_count');
	var title = Dase.getMeta('query');
	var pairs = 'url='+pageurl+'&title='+title+'&count='+count;
	Dase.ajax(url,'post',null,pairs,Dase.user.eid,Dase.user.htpasswd,content_headers); 
}
