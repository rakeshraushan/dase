{extends file="admin/layout.tpl"}
{block name="title"}DASe: Upload Item{/block} 

{block name="content"}
<div class="list" id="browse">

	<!-- set up uploader widgets -->
	<script language="Javascript">
		{literal}
		$(document).ready(function() {
			result_queue = new Dase.widget.messagequeue(document.getElementById('results'));
			$('div.uploader').each(function() {
				Dase.widget.uploader.init(
				this, 
				function(parsed_result, uploader) {
					if(parsed_result.status == 'ok') {
						if(parsed_result.filesize > 1000000) var filesize = Math.ceil(parseInt(parsed_result.filesize)/1000000)+'MB';
						else var filesize = Math.ceil(parseInt(parsed_result.filesize)/1000)+'KB';
						var message = parsed_result.filename+' ['+filesize+']'+'<a target="view_ul" href="'+parsed_result.href+'">saved</a>';
						} else {
						var message = parsed_result.message;
						if(parsed_result.filename) message = message + ': ' + parsed_result.filename;
						if(parsed_result.filesize) message = message + ' [' + parsed_result.filesize + ']';
					}
					result_queue.push(message, parsed_result.status);
					$('div#'+uploader._form.id.replace(/_form/, '')+' > *').hide();
				},
				function(uploader) {
					$('div#'+uploader._form.id.replace(/_form/, '')+' > *').hide();
					var spinner = new Image();
					spinner.src = 'images/spinner.gif';
					var filename = document.createElement('span');
					filename.innerHTML = uploader._input.value;
					$('div#'+uploader._form.id.replace(/_form/, '')).append(spinner).append(filename);
					var pos = parseInt(uploader._form.id.replace(/uploader_([0-9]+)_form/, '$1')) - 1;
					$('div#uploader_'+pos).show();
				}
				)
			});
			$('div.uploader:first').show();
		});
		{/literal}
	</script>

	{if $msg}<h3 class="alert">{$msg}</h3>{/if}

	<!-- start cranking out uploaders with hidden iframes -->
	<!-- that skeleton wearing his bones like a broiler (Anne Sexton, Godfather Death)-->
	{section name=uploader loop=30}
	<div class="uploader" id="uploader_{$fi}">
		<form name="uploader_{$fi}_form" id="uploader_{$fi}_form" method="post" class="uploader" enctype="multipart/form-data">
			<input type="file" id="uploader_{$fi}_file" name="uploader_{$fi}_file" class="uploader_file" />
			<br /><!--span class="status" id="uploader_{$fi}_status"></span-->
		</form>
		<iframe name="uploader_{$fi}_target" id="uploader_{$fi}_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>
	{/section}

	<div id="results">

	</div>



	<h1>Check your Atom Document:</h1>
	<!--
	<form action="cb/{$user->eid}/{$collection->ascii_id}/check_atom" method="post" enctype="multipart/form-data">
		<p>
		<input type="file" name="atom"/>
		<input type="submit" value="check sytntax"/>
		</p>
	</form>
	-->
</div>
{/block}
