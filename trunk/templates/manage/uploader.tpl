{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/uploader.js"></script>
{/block}

{block name="title"}DASe: Upload Item{/block} 

{block name="content"}
<div class="full">

	{if $msg}<h3 class="alert">{$msg}</h3>{/if}

	<h1>Upload Files</h1>

	<div class="uploader" id="uploader_1">
		<form name="uploader_1_form" id="uploader_1_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_1_file" size="50"/>
			<input type="hidden" name="num" value="1" />
		</form>
		<iframe name="uploader_1_target" id="uploader_1_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_2">
		<form name="uploader_2_form" id="uploader_2_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_2_file" size="50"/>
			<input type="hidden" name="num" value="2" />
		</form>
		<iframe name="uploader_2_target" id="uploader_2_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_3">
		<form name="uploader_3_form" id="uploader_3_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_3_file" size="50"/>
			<input type="hidden" name="num" value="3" />
		</form>
		<iframe name="uploader_3_target" id="uploader_3_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_4">
		<form name="uploader_4_form" id="uploader_4_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_4_file" size="50"/>
			<input type="hidden" name="num" value="4" />
		</form>
		<iframe name="uploader_4_target" id="uploader_4_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_5">
		<form name="uploader_5_form" id="uploader_5_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_5_file" size="50"/>
			<input type="hidden" name="num" value="5" />
		</form>
		<iframe name="uploader_5_target" id="uploader_5_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_6">
		<form name="uploader_6_form" id="uploader_6_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_6_file" size="50"/>
			<input type="hidden" name="num" value="6" />
		</form>
		<iframe name="uploader_6_target" id="uploader_6_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_7">
		<form name="uploader_7_form" id="uploader_7_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_7_file" size="50"/>
			<input type="hidden" name="num" value="7" />
		</form>
		<iframe name="uploader_7_target" id="uploader_7_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_8">
		<form name="uploader_8_form" id="uploader_8_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_8_file" size="50"/>
			<input type="hidden" name="num" value="8" />
		</form>
		<iframe name="uploader_8_target" id="uploader_8_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_9">
		<form name="uploader_9_form" id="uploader_9_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_9_file" size="50"/>
			<input type="hidden" name="num" value="9" />
		</form>
		<iframe name="uploader_9_target" id="uploader_9_target">
		</iframe>
	</div>

	<div class="uploader hide" id="uploader_10">
		<form name="uploader_10_form" id="uploader_10_form" method="post" enctype="multipart/form-data">
			<input type="file" name="uploader_10_file" size="50"/>
			<input type="hidden" name="num" value="10" />
		</form>
		<iframe name="uploader_10_target" id="uploader_10_target">
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

	<ul id="results">
		<li class="hide" id="results_10"></li>
		<li class="hide" id="results_9"></li>
		<li class="hide" id="results_8"></li>
		<li class="hide" id="results_7"></li>
		<li class="hide" id="results_6"></li>
		<li class="hide" id="results_5"></li>
		<li class="hide" id="results_4"></li>
		<li class="hide" id="results_3"></li>
		<li class="hide" id="results_2"></li>
		<li class="hide" id="results_1"></li>
	</ul>

	<h2>{$recent_uploads->title}</h2>
	<ul id="recent">
		{foreach item=item from = $recent_uploads->entries}
		<li><img src="{$item->thumbnailLink}"/><br/><a href="{$item->link}">{$item->title}</a></li>
		{/foreach}
	</ul>
</div>
{/block}
