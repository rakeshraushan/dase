{extends file="admin/layout.tpl"}

{block name="js_include"}admin/uploader.js{/block}

{block name="title"}DASe: Upload Item{/block} 

{block name="content"}
<div class="full">

	{if $msg}<h3 class="alert">{$msg}</h3>{/if}

	<h1>Upload Files</h1>

	<div class="uploader" id="uploader_1">
		<form name="uploader_1_form" id="uploader_1_form" method="post" enctype="multipart/form-data">
			<input type="file" id="uploader_1_file" name="uploader_1_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="1" />
			<br /><!--span class="status" id="uploader_1_status"></span-->
		</form>
		<iframe name="uploader_1_target" id="uploader_1_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_2">
		<form name="uploader_2_form" id="uploader_2_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_2_file" name="uploader_2_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="2" />
			<br /><!--span class="status" id="uploader_2_status"></span-->
		</form>
		<iframe name="uploader_2_target" id="uploader_2_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_3">
		<form name="uploader_3_form" id="uploader_3_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_3_file" name="uploader_3_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="3" />
			<br /><!--span class="status" id="uploader_3_status"></span-->
		</form>
		<iframe name="uploader_3_target" id="uploader_3_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_4">
		<form name="uploader_4_form" id="uploader_4_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_4_file" name="uploader_4_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="4" />
			<br /><!--span class="status" id="uploader_4_status"></span-->
		</form>
		<iframe name="uploader_4_target" id="uploader_4_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_5">
		<form name="uploader_5_form" id="uploader_5_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_5_file" name="uploader_5_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="5" />
			<br /><!--span class="status" id="uploader_5_status"></span-->
		</form>
		<iframe name="uploader_5_target" id="uploader_5_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_6">
		<form name="uploader_6_form" id="uploader_6_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_6_file" name="uploader_6_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="6" />
			<br /><!--span class="status" id="uploader_6_status"></span-->
		</form>
		<iframe name="uploader_6_target" id="uploader_6_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_7">
		<form name="uploader_7_form" id="uploader_7_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_7_file" name="uploader_7_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="7" />
			<br /><!--span class="status" id="uploader_7_status"></span-->
		</form>
		<iframe name="uploader_7_target" id="uploader_7_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_8">
		<form name="uploader_8_form" id="uploader_8_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_8_file" name="uploader_8_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="8" />
			<br /><!--span class="status" id="uploader_8_status"></span-->
		</form>
		<iframe name="uploader_8_target" id="uploader_8_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_9">
		<form name="uploader_9_form" id="uploader_9_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_9_file" name="uploader_9_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="9" />
			<br /><!--span class="status" id="uploader_9_status"></span-->
		</form>
		<iframe name="uploader_9_target" id="uploader_9_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_10">
		<form name="uploader_10_form" id="uploader_10_form" method="post" class="uploader hide" enctype="multipart/form-data">
			<input type="file" id="uploader_10_file" name="uploader_10_file" size="50"  class="uploader_file" />
			<input type="hidden" name="num" value="10" />
			<br /><!--span class="status" id="uploader_10_status"></span-->
		</form>
		<iframe name="uploader_10_target" id="uploader_10_target" class="uploader_target" style="display: none;">
		</iframe>
	</div>

	<ul id="queue">
		<li class="hide" id="queue_1"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_2"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_3"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_4"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_5"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_6"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_7"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_8"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_9"><img src="www/images/indicator.gif"/></li>
		<li class="hide" id="queue_10"><img src="www/images/indicator.gif"/></li>
	</ul>

	<div id="results">
	</div>

</div>
{/block}
