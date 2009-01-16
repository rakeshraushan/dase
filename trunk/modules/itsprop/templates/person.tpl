{extends file="layout.tpl"}

{block name="content"}
<h1>User Information for {$person|select:'person_name'} ({$person|select:'person_eid'})</h1>
<div class="main">
	<form>
		<p>
		<label for="email">Email</label>
		<input type="text" name="email" value="{$person|select:'person_email'}"/>
		</p>
		<p>
		<label for="phone">Phone</label>
		<input type="text" name="phone" value="{$person|select:'person_phone'}"/>
		</p>
		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
</form>
</div>

{/block}

