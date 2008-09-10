//from http://www.hesido.com/web.php?page=atomidtimestamp
function padzero(toPad) {
	return (toPad+'').replace(/(^.$)/,'0$1');
}

function paddoublezero(toPad) {
	return (toPad+'').replace(/(^.$)/,'0$1').replace(/(^..$)/,'0$1');
}

Dase.pageInit = function() {
	var form = Dase.$('newCollection');
	form.onsubmit = function() {
		data = {};
		data.collection_name = form.collection_name.value;
		data.eid = Dase.user.eid;
		var d = new Date();
		var utcf = d.getTimezoneOffset();
		var ypad = d.getFullYear();
		var ypadutc = d.getUTCFullYear();
		var mpad = padzero(d.getMonth()+1);
		var mpadutc = padzero(d.getUTCMonth()+1);
		var dpad = padzero(d.getDate());
		var dpadutc = padzero(d.getUTCDate());
		var SHour = padzero(d.getHours());
		var SHourutc = padzero(d.getUTCHours());
		var SMins = padzero(d.getMinutes());
		var SMinsutc = padzero(d.getUTCMinutes());
		var SSecs = padzero(d.getSeconds());
		var SSecsutc = padzero(d.getUTCSeconds());
		var SMlsc = paddoublezero(d.getMilliseconds());
		var SMlscutc = paddoublezero(d.getUTCMilliseconds());
		var uOhr = padzero(Math.floor(Math.abs(utcf) / 60));
		var uOmn = padzero(Math.abs(utcf) - Math.floor(Math.abs(utcf) / 60) * 60);
		var oFsg = (utcf < 0) ? '-' : '+';

		var datestring = ypad+'-'+mpad+'-'+dpad+'T'+SHour+':'+SMins+':'+SSecs+oFsg+uOhr+':'+uOmn
		//todo: needs work!!!!!!!!!!!!!:
		var ascii_id = form.collection_name.value.replace(/(collection|archive)/i,'').replace(/ /gi,"_").replace(/(__|_$)/g,'').toLowerCase();
		data.id = Dase.base_href+'collection/'+ascii_id; 
		data.date = datestring
		data.ascii_id = ascii_id; 
		var templateObj = TrimPath.parseDOMTemplate("atom_jst");
		var atom = Dase.util.trim(templateObj.process(data));
		var content_headers = {
			'Content-Type':'application/atom+xml;type=entry; charset=UTF-8;',
			//'Content-Type':'application/json',
			'Content-MD5':hex_md5(atom),
		}
		if (confirm("You are about to create collection with ascii id\n\n"+ascii_id+"\n\nOK?")) {
			Dase.ajax(Dase.base_href='collections','post',function(resp) {
					alert(resp);
					},atom,Dase.user.eid,Dase.user.htpasswd,content_headers);
		}
		return false;
	}
};
