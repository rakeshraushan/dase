if (!Dase) { var Dase = {}; }

jQuery(function(){ 
		Dase.listMenu();
		Dase.multicheck("checkedCollection");
		Dase.getItemTallies();
		Dase.prepareBrowse();
		}); 

Dase.listMenu = function() { 
	jQuery("ul#menu").find("li/ul").hide().end().find("li[ul]").find("a:eq(0)").click(function() {
			jQuery(this).find('../ul').toggle();
			return false;
			});
}

Dase.multicheck = function(c) { 
	jQuery("ul.multicheck").append("<a href='' class='chk'>check all</a><a href='' class='unchk'>uncheck all</a>").find("input:checkbox").click( function() { jQuery(this).parent().toggleClass(c);}).filter("@checked=checked").parent().addClass(c);
	jQuery(".chk").hide().click(function() { 
			jQuery(this).hide().next().show().parent().find("input:checkbox").attr("checked",true).parent().addClass(c);
			return false;
			});
	jQuery(".unchk").click(function() { 
			jQuery(this).hide().prev().show().parent().find("input:checkbox").removeAttr("checked").parent().removeClass(c);
			return false;
			});
}

Dase.getItemTallies = function() {
	if (jQuery("#collectionList").size()) {
		jQuery.get("ajax/item_tallies",function(data){
				jQuery("collection",data).each(function() {
					jQuery('#tally-'+jQuery(this).attr("id")).text("(" + jQuery(this).attr('item_tally') + ")");
					});
				});
	}
}

Dase.prepareBrowse = function() {
	if(jQuery("#browseColumns").size()) {
		var params = jQuery("#browseColumns").attr("class").split(" ");
		var collection_id = params[0];
		var public_only = params[1];
		Dase.getAttributes(0,collection_id,public_only);
		jQuery("#catColumn").find("a").each(function() {
				var category_id = jQuery(this).attr("id").split('_').pop(); //creates closure for click event
				jQuery(this).click(function() {
					jQuery("#attColumn").removeClass();
					jQuery('#valColumn').html(" ").attr("class","empty");
					if ('admin' == category_id) {
					Dase.getAdminAttributes(collection_id);
					} else {
					Dase.getAttributes(category_id,collection_id,public_only);
					}
					return false;
					});
				});
	}
}

Dase.getAttributes = function(cat_id,collection_id,public_only) {
	var params = {
	   token: new Date().getTime(),
	   link_class: "att_link",
	   collection_id: collection_id,
	   cat_id: cat_id,
	   public_only: public_only
	};	   
	jQuery("#catColumn/a[@class=spill]").attr("class","catLink");
	jQuery("#catLink_"+cat_id).attr("class","spill");
	jQuery("#attColumn").html("<div class='loading'>Loading Attributes...</div>");
	jQuery.get("ajax/attributes",params,function(data) {
			jQuery("#attColumn").html(data);
			Dase.bindGetValues(collection_id);
			});
	if (jQuery("#attColumn").attr("class")) {
		//meaning there is an attr_id embedded in the className of attColumn
		jQuery("#valColumn").removeClass();
	} else {
		jQuery('#valColumn').html(" ").attr("class","empty");
	}
	jQuery('#autocomplete').html(" ");
}

Dase.getAdminAttributes = function(collection_id) {
	var params = {
	   token: new Date().getTime(),
	   link_class: "att_link",
	   collection_id: collection_id
	};	   
	jQuery("#catColumn/a[@class=spill]").attr("class","catLink");
	jQuery("#catLink_admin").attr("class","spill");
	jQuery("#attColumn").html("<div class='loading'>Loading Admin Attributes...</div>");
	jQuery.get("ajax/admin_attributes",params,function(data) {
			jQuery("#attColumn").html(data);
			Dase.bindGetValues(collection_id);
			});
	if (jQuery("#attColumn").attr("class")) {
		//meaning there is an attr_id embedded in the className of attColumn
		jQuery("#valColumn").removeClass();
	} else {
		jQuery('#valColumn').html(" ").attr("class","empty");
	}
	jQuery('#autocomplete').html(" ");
}

Dase.bindGetValues = function(collection_id) {
	jQuery("#attColumn").find('a').each(function() {
			var attribute_id = jQuery(this).attr("class").split(" ").pop(); //creates closure for click event
			jQuery(this).click(function() {
				var params = {
					token: new Date().getTime(),
					attribute_id: attribute_id,
					collection_id: collection_id
					};	   
					jQuery("#attColumn//a[@class=spill]").attr("class","att_link");
					jQuery("#att_link_"+attribute_id).attr("class","spill");
					jQuery("#valColumn").html("<div class='loading'>Loading Values...</div>");
					jQuery.get("ajax/values_by_attribute",params,function(data) {
						jQuery("#valColumn").html(data).removeClass();
						});
					jQuery('#autocomplete').html(" ");
					window.scroll(0,0);
					return false;
					});
			});
	Dase.getAttributeTallies(collection_id);
}

Dase.getAttributeTallies = function(coll_id) {
	if (jQuery("#getTallies").size()) {
		var params = {
         token: new Date().getTime(),
		 coll_id: coll_id
		};	   
		if ('adminAtts' == jQuery('#getTallies').attr("class")) { 
			params.admin = 1;
		}
		if (jQuery('#currently_using_cb').size()) {
			params.cb=1;
		}
		jQuery.get("ajax/attribute_tallies",params,function(data) {
				jQuery("attribute",data).each(function() {
					jQuery('#tally-'+jQuery(this).attr("id")).text("(" + jQuery(this).attr('val_tally') + ")");
					});

				});
	}
}
