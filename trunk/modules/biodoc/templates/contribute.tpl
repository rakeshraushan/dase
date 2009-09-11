{extends file="layout.tpl"}

{block name="main"}

<div class="pagetitle">Contribute</div>
<p>
If you have an online resource you would like to contribute to Biology Digital Online Content, please use the form below:
</p>
<div class="contribute_content">
	<form action="{$module_root}emailer" method="post">
		<label>Title:</label>
		<input type="text" size="35" name="txtTitle">
		<label>Type:</label>
		<input type="text" size="35" name="txtType">
		<label>Description:</label><textarea rows="4" name="txtDescription" cols="27"></textarea>
		<label>Unit:</label>
		<input type="text" size="35" name="txtUnit">
		<label>Topic:</label> 
		<input type="text" size="35" name="txtTopic">
		<label>Keywords:</label>
		<input type="text" size="35" name="txtKeywords">
		<label>Format:</label>
		<input type="text" size="35" name="txtFormat">
		<label>Required Plug-in:</label>
		<input type="text" size="35" name="txtPlugin">
		<label>Size:</label> 
		<input type="text" size="35" name="txtSize">
		<label>Duration:</label>
		<input type="text" size="35" name="txtDuration">
		<label>URL:</label>
		<input type="text" size="35" name="txtURL">
		<label>Name:</label>
		<input type="text" size="35" name="txtName">
		<label>Affiliation:</label>
		<input type="text" size="35" name="txtAffiliation">
		<label>Email:</label>
		<input type="text" size="35" name="txtEmail">
		<label>Acknowledgements:</label>
		<input type="text" size="35" name="txtAcknowledgements">
		<p>
		<input type="submit" value="submit">
		</p>
	</form>
</div>
{/block}

