<div id="sidebar">

	<ul id="menu">
		<li class="home"><a href="" class="main">Home/Search</a></li>
		<li class="cart"><a href="" class="main">My Cart</a>
		  <ul class="hide" id="cart">
			<li><a href="" class="create">view cart</a></li>
			<li><a href="" class="create">empty cart</a></li>
			<li><a href="" class="create" id="moveCartTo">move cart items to...</a>
			<ul class="hide">
			   <li>
			   <form action="sss" method="post">
		       <div id="tagsSelect">
			    <!-- ajax fills in here -->
			   </div>
			   <div><input type="submit" value="move items"></div>
			   </form>
			   </li>
			 </ul>
			</li>

		  </ul>
		</li>

		<li class="user_collection"><a href="" class="main">My Collections</a>
		  <ul class="hide" id="user_collection">
			     <li class="menuForm">
			       <form action="sss" method="post">
				    <div><input type="text" name="coll_name"></div>
				    <div><input type="submit" value="create collection"></div>
			       </form>
		        </li>
			     <!-- ajax fills in here -->
		   </ul>
		  </li>

		<li class="slideshow"><a href="" class="main">My Slideshows</a>
		  <ul class="hide" id="slideshow">
		    <li class="placeholder"></li>
		  </ul>
		</li>

		<li class="subscription"><a href="" class="main">My Subscriptions</a>
		  <ul class="hide" id="subscription">
		    <li class="placeholder"></li>
		  </ul>
		</li>
	</ul>

	<div class="loadingMsg" id="ajaxMenuMsg"></div>

</div> <!-- closes sidebar -->
