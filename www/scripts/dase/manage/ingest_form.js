//from http://www.hesido.com/web.php?page=atomidtimestamp
function padzero(toPad) {
	return (toPad+'').replace(/(^.$)/,'0$1');
}

function paddoublezero(toPad) {
	return (toPad+'').replace(/(^.$)/,'0$1').replace(/(^..$)/,'0$1');
}

Dase.pageInit = function() {
	var form = Dase.$('ingestCollectionForm');
	var ind = Dase.$('indicator');
	ind.innerHTML = '<img src="www/images/indicator.js"/>';
	form.onsubmit = function() {
		startColors('throbber');
		Dase.removeClass(ind,'hide');
		var content_headers = {
			'Content-Type':'application/x-www-form-urlencoded'
		}
		Dase.ajax(Dase.base_href+'/manage/ingest/checker','post',function(resp) {
			var parts = resp.split('|');
			if ('ok' == parts[0]) {
				ind.innerHTML = 'valid DASe Collection URL with '+parts[1]+' items';
			} else {
				ind.innerHTML = '';
				alert('sorry, that is not a valid DASe Collection');
			}
		},Dase.form.serialize(this),null,null,content_headers);
		return false;
	}
};
