{extends file="layout.tpl"}

{block name="head-links"}
<link rel="http://daseproject.org/relation/edit" type="application/json" href="{$dept->jsonEditLink}"/>
<link rel="edit" type="application/atom+xml" href="{$dept->editLink}"/>
{/block}

{block name="content"}
<h1>Department: {$dept->dept_name.text} ({$dept->dept_id.text})</h1>
<div class="main">
	<form id="deptForm" class="shortForm" method="post" >
		<input type="hidden" name="id" value="{$dept->dept_id.text}"/>
		<p>
		<label for="name">Name</label>
		<input type="text" class="long" name="name" value="{$dept->dept_name.text}"/>
		</p>
		<p>
		<label for="chair">Chair</label>
		<input type="text" name="chair" value="{$dept->dept_chair.text}"/>
		</p>
		<p>
		<label for="email">Chair Email</label>
		<input type="text" name="chair_email" value="{$dept->dept_chair_email.text}"/>
		</p>
		<p>
		<label for="email">Chair Eid</label>
		<input type="text" name="chair_eid" value="{$dept->dept_chair_eid.text}"/>
		</p>
		<p>
		<label for="display">Display?</label>
		<input type="radio" name="display" {if $dept->dept_display.text == 'yes'}checked{/if} value="yes"/> yes
		<input type="radio" name="display" {if $dept->dept_display.text == 'no'}checked{/if} value="no"/> no
		</p>
		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
	<dl class="current">
		<dt>name</dt> <dd> {$dept->dept_name.text|default:'--'}</dd>
		<dt>id</dt> <dd> {$dept->dept_id.text|default:'--'}</dd>
		<dt>chair</dt> <dd> {$dept->dept_chair.text|default:'--'}</dd>
		<dt>chair eid</dt> <dd> {$dept->dept_chair_eid.text|default:'--'}</dd>
		<dt>email</dt> <dd> {$dept->dept_chair_email.text|default:'--'}</dd>
		<dt>phone</dt> <dd> {$dept->dept_phone.text|default:'--'}</dd>
		<dt>display?</dt> <dd> {$dept->dept_display.text|default:'--'}</dd>
	</dl>
	<dl class="cola">
		<h3 class="infoBar">Department Info from CoLA</h3>
		<dt>name</dt> <dd>{$cola_dept.name}</dd>
		<dt>id</dt> <dd>{$cola_dept.id}</dd>
		<dt>chair</dt> <dd>{$cola_dept.chair}</dd>
		<dt>email</dt> <dd>{$cola_dept.email}</dd>
		<dt>phone</dt> <dd>{$cola_dept.phone}</dd>
	</dl>
</div>
{/block}

