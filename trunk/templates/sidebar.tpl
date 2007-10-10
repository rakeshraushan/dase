<div id="sidebar">

	<ul id="menu">
		<li class="home"><a href="">Home/Search</a></li>
		<li class="cart"><a href="">My Cart</a>
		<ul>
			<li><a href="" class="create">view cart</a></li>
			<li><a href="" class="create">empty cart</a></li>
			<li><a href="" class="create" id="showCollForm">move cart items to...</a>
		<ul>
			<li><form action="sss" method="post">
			
			<div id="tagsSelect">
			<!-- ajax fills in here -->
			</div>
			<input type="submit" value="move items">
			</form>
			</li>
				</ul>
			</li>
		</ul>
		</li>
		<li class="collection"><a href="">My Collections</a>
		<ul>
			<li><a href="" class="create">new collection...</a>
			<ul>
			<li><form action="sss" method="post">
				<input type="text" size="18" name="coll_name"/>
				<input type="submit" value="create collection">
			</form>
			</li>
			</ul>
			</li>
			<div id="user_collection">
			<!-- ajax fills in here -->
			</div>
		</ul>
		</li>
		<li class="slideshow"><a href="">My Slideshows</a>
		<ul>
			<div id="slideshow">
			<!-- ajax fills in here -->
			</div>
		</ul>
		</li>
		<li class="subscription"><a href="">My Subscriptions</a>
		<ul>
			<div id="subscription">
			<!-- ajax fills in here -->
			</div>
		</ul>
		</li>
	</ul>

</div> <!-- closes sidebar -->
