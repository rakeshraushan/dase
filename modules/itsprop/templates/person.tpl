{extends file="layout.tpl"}

{block name="content"}
<h1>User Information</h1>
<div class="main">
	<form>
		<p>
		<label for="name">Eid</label>
		<input type="text" name="eid" value="{$type->name}"/>
		</p>
		<p>
		<label for="name">Name</label>
		<input type="text" name="name" value="{$type->name}"/>
		</p>
		<p>
		<label for="name">Email</label>
		<input type="text" name="email" value="{$type->name}"/>
		</p>
		<p>
		<label for="name">Phone</label>
		<input type="text" name="phone" value="{$type->name}"/>
		</p>
		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
</form>
</div>

{/block}

