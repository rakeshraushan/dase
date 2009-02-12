{extends file="layout.tpl"}

{block name="head-links"}
<link rel="dept_props" href="{$props_link}" />
{/block}

{block name="head"}
<script type="text/javascript" src="scripts/dept_props.js"></script>
{/block}

{block name="content"}
<div id="vision_page" class="main">
	<h1>Proposals for {$dept->dept_name.text}</h1>
	<h4>Chairperson: {$dept->dept_chair.text}</h4>

	<h2>Vision Statement</h2>
	<div class="vision_instruction">
		<p>
		Please assist us in the grant review process by writing a departmental
		Vision Statement that explains how each proposal fits your department's
		goals and plans. After you complete your vision statement, please rank the
		proposals according to departmental priorities established in the Vision
		Statement. Your ranking will be one of the major criteria the IT Grant
		Review Committee uses in making its funding decisions.
		</p>


		<h4>The Vision Statement must include:</h4>

		<p>
		1) A short statement of the department's instructional
		technology plan with an explanation of how it addresses
		pedagogical goals.
		</p>

		<p><strong>-AND-</strong></p>

		<p>
		2) A brief explanation of how each proposed project fits
		(or does not fit) departmental goals and plans.
		</p>
	</div>

	<div id="vision_statement">{$dept->content|nl2br}</div>
	<form method="post" action="{$dept->editContentLink}" class="hide" id="vision_form">
		{assign var=rows value=$dept->content|count_words}
		<textarea rows="{$rows/11}" id="vision_text" name="vision">{$dept->content}</textarea>
		<p>
		<input type="submit" value="update">
		</p>
	</form>
	<p><a href="ss" id="toggle_vision">add/edit vision statement</a></p>

	<h2>Proposals</h2>

	<div class="vision_instruction">
		Please rank the proposals numerically in the space below.  You can enter
		each proposal's rank number and click "update sort order" - OR -  move a
		single item to the top with 'top' arrow.
	</div>
	<div id="propsList">
		<h2 id="loadingProps"><img src="{$app_root}www/images/indicator.gif"> loading proposals...</h2>
	</div>
</div>
{/block}

