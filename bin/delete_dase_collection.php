<?php

include 'config.php';


//$action = "count";
$action = "delete";

$coll = 'vrc_works';

$c = Dase_DBO_Collection::get($db,$coll);

print "$c->collection_name ($c->item_count)\n\n";



$dbh = $db->getDbh();

$sql = "SELECT count(value.id) from value,item WHERE item.collection_id = $c->id and value.item_id = item.id";
$sth = $dbh->prepare($sql);
$sth->execute();
$count = $sth->fetchColumn();
print "values: $count\n";

if ('delete' == $action) {
	print "values\n";
	$i = 0;
	$sql = "SELECT value.id from value,item WHERE item.collection_id = $c->id and value.item_id = item.id";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	while ($id = $sth->fetchColumn()) {
		$i++;
		$v = new Dase_DBO_Value($db);
		$v->load($id);
		print "$i of $count deleting -- $v->value_text\n";
		$v->delete();
	}
}


if ('count' == $action) {
	$sql = "SELECT count(v.id) from defined_value v, attribute a
		WHERE v.attribute_id = a.id 
		AND	a.collection_id = $c->id
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "defined: $count\n";
} elseif ('delete' == $action) {
	print "defined values\n";
	$sql = "SELECT v.id from defined_value v, attribute a
		WHERE v.attribute_id = a.id 
		AND	a.collection_id = $c->id
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	while ($id = $sth->fetchColumn()) {
		$defined = new Dase_DBO_DefinedValue($db);
		if ($defined->load($id)) {
			$defined->delete();
			print "deleted defined value\n";
		}
	}
}

if ('count' == $action) {
	$sql = "SELECT count(in.id) from input_template in, attribute a
		WHERE in.attribute_id = a.id 
		AND	a.collection_id = $c->id
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "input templates: $count\n";
} elseif ('delete' == $action) {
	print "input template\n";
	$id = null;
	$sql = "SELECT in.id from input_template in, attribute a
		WHERE in.attribute_id = a.id 
		AND	a.collection_id = $c->id
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	while ($id = $sth->fetchColumn()) {
		$inp = new Dase_DBO_InputTemplate($db);
		if ($inp->load($id)) {
			$inp->delete();
			print "deleted input\n";
		}
	}
}

if ('count' == $action) {
	$sql = "SELECT count(id) from attribute a
		WHERE a.collection_id = $c->id
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "attributes: $count\n";
} elseif ('delete' == $action) {
	print "attributes\n";
	$sql = "DELETE from attribute 
		WHERE collection_id = $c->id
		";
	$count = $dbh->exec($sql);
	print "deleted $count attributes\n";
}

if ('count' == $action) {
	$sql = "SELECT count(id) from collection_manager 
		WHERE collection_manager.collection_ascii_id = '$c->ascii_id'
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "managers: $count\n";
} elseif ('delete' == $action) {
	print "managers\n";
	$sql = "DELETE from collection_manager 
		WHERE collection_manager.collection_ascii_id = '$c->ascii_id'
		";
	$count = $dbh->exec($sql);
	print "deleted $count managers\n";
}


if ('count' == $action) {
	$sql = "SELECT count(id) from content 
		WHERE content.p_collection_ascii_id = '$c->ascii_id'
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "content: $count\n";
} elseif ('delete' == $action) {
	print "content\n";
	$sql = "DELETE from content 
		WHERE content.p_collection_ascii_id = '$c->ascii_id'
		";
	$count = $dbh->exec($sql);
	print "deleted $count content\n";
}

if ('count' == $action) {
	$sql = "SELECT count(id) from comment 
		WHERE comment.p_collection_ascii_id = '$c->ascii_id'
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "comment: $count\n";
} elseif ('delete' == $action) {
	print "comments\n";
	$sql = "DELETE from comment 
		WHERE comment.p_collection_ascii_id = '$c->ascii_id'
		";
	$count = $dbh->exec($sql);
	print "deleted $count comments\n";
}

if ('count' == $action) {
	$sql = "SELECT count(id) from media_file 
		WHERE media_file.p_collection_ascii_id = '$c->ascii_id'
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "media_file: $count\n";
} elseif ('delete' == $action) {
	print "media\n";
	$sql = "DELETE from media_file 
		WHERE media_file.p_collection_ascii_id = '$c->ascii_id'
		";
	$count = $dbh->exec($sql);
	print "deleted $count media_files\n";
}

if ('count' == $action) {
	$sql = "SELECT count(id) from item_type 
		WHERE item_type.collection_id = $c->id
		";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "item types: $count\n";
} elseif ('delete' == $action) {
	print "item_types\n";
	$sql = "DELETE from item_type 
		WHERE item_type.collection_id = $c->id
		";
	$count = $dbh->exec($sql);
	print "deleted $count item_types\n";
}

$sql = "SELECT count(id) from item 
	WHERE item.collection_id = $c->id";
$sth = $dbh->prepare($sql);
$sth->execute();
$count = $sth->fetchColumn();
print "items: $count\n";

if ('delete' == $action) {
	print "items\n";
	$sql = "DELETE from item
		WHERE item.collection_id = $c->id";
	$count = $dbh->exec($sql);
	print "deleted $count items\n";
}

if ('count' == $action) {
	$sql = "SELECT count(id) 
		FROM search_table 
		WHERE collection_id = $c->id";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "search table: $count\n";
} elseif ('delete' == $action) {
	print "search tables\n";
	$sql = "DELETE from search_table 
		WHERE collection_id = $c->id
		";
	$count = $dbh->exec($sql);
	print "deleted $count search tables\n";
}

if ('count' == $action) {
	$sql = "SELECT count(id) 
		FROM admin_search_table 
		WHERE collection_id = $c->id";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "admin search table: $count\n";
} elseif ('delete' == $action) {
	print "admin search tables\n";
	$sql = "DELETE from admin_search_table
		WHERE collection_id = $c->id
		";
	$count = $dbh->exec($sql);
	print "deleted $count admin search table\n";
}

if ('count' == $action) {
	$sql = "SELECT count(*) from tag_item 
		WHERE p_collection_ascii_id = '".$c->ascii_id."'";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "tag_items: $count\n";
} elseif ('delete' == $action) {
	print "tag_items\n";
	$sql = "DELETE from tag_item 
		WHERE p_collection_ascii_id = '".$c->ascii_id."'";
	$count = $dbh->exec($sql);
	print "deleted $count tag items\n";
}

if ('count' == $action) {
	$sql = "SELECT count(id) 
		FROM value_revision_history v 
		WHERE v.collection_ascii_id = '$c->ascii_id'";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$count = $sth->fetchColumn();
	print "revisions: $count\n";
} elseif ('delete' == $action) {
	print "rev history\n";
	$sql = "DELETE 
		FROM value_revision_history 
		WHERE collection_ascii_id = '$c->ascii_id'";
	$count = $dbh->exec($sql);
	print "deleted $count revisions\n";
}

if ('delete' == $action) {
	$c->delete();
	print "deleted collection\n";
}

