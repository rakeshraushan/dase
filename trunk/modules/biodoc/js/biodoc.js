function getEntries(feed){
	var entries = new Array();
	$(feed.childNodes[0]).find('entry').each(function(i,n){
		entries[i] = $(n).clone();
	});
	return entries;
}
function htmlEncode(value){ 
	return $('<div/>').text(value).html(); 
} 

function htmlDecode(value){ 
	return $('<div/>').html(value).text(); 
}

function getSerialNumber(entry){
	return $(entry).find('category[scheme="http://daseproject.org/category/serial_number"]').attr('term');
}

function getTopics(feed){
	var topics = new Array();
	//$(feed.childNodes[0]).find('category[term="topic"]').each(function(i,n){
	$(feed).find('category[term="topic"]').each(function(i,n){
		topics[i] = $(n).clone();
	});
	return topics;
}

function sortTopics(a,b) {
	var x = a.text().toLowerCase();
	var y = b.text().toLowerCase();
	if (x < y) return -1
	if (x > y) return 1
	return 0
}


$(document).ready(function(){

	var json_url = $("link[rel='topics']").attr('href'); 

	$.getJSON(json_url, function(data){
		var option_list_units = '<option selected="selected" value="0">--Select Unit--</option>';
		$.each(data.values, function(){
			value = this;
			option_list_units += '<option value="'+value['v']+'">'+value['v']+' ('+value['t']+')</option>\n';
		});
		$('#unitFormSelect').append(option_list_units); 
		//new AutoSuggest(document.getElementById('kwterm'),Onda.keywords);
	});

	$('#unitFormSelect').change( function() {
		$('#retrieveTopics').show();
		$("select option:selected").each(function () {
			var unit = $(this).attr('value');

			var date = new Date();
			var search_url = Dase.base_href+'search.atom';

			data = { 'c':'biodoc','q':'@unit:"'+unit+'"', 'max':9999,'cache_buster':date.getTime()};
			var option_list_topics = '<option selected="selected" value="0">--Select Topic--</option>';
			$.get(search_url,data,function(feed) {
				var topics = getTopics(feed);
				topics = topics.sort(sortTopics);
				var aggregated_topics = {};
				for (var i=0;i<topics.length;i++) {
					t_text = topics[i].text()
					if (!aggregated_topics[t_text]) {
						aggregated_topics[t_text] = 1;
					} else {
						aggregated_topics[t_text] += 1;
					}
				}

				var seen = {};
				/*
				for (var i=0;i<topics.length;i++) {
					var topic = topics[i];
					var txt = topic.text();
					if (!seen[txt]) {
						option_list_topics += '<option value="'+txt+'">'+txt+'</option>\n';
						seen[txt] = 1;
					}
				} 
				*/
				for (var topic in aggregated_topics) {
					option_list_topics += '<option value="'+topic+'">'+topic+' ('+aggregated_topics[topic]+')</option>\n';
				} 
				$('#topicSelect').show().html(option_list_topics); 
				$('#retrieveTopics').hide();
			},'xml');
			return false;
		});
	});

});
