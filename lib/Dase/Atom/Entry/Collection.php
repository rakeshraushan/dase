<?php
class Dase_Atom_Entry_Collection extends Dase_Atom_Entry
{
	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,$root);
	}

	function getName() 
	{
		//how name is modelled in Atom
		return $this->getTitle();
	}

	function getAscii_id() 
	{
		//how ascii_id is modelled in Atom
		return $this->getContent();
	}

	function create()
	{
		//todo: collection creation code
		//from dase 1:
		//
		if ($user->eid != $super_admin_eid) {
			header("Location:$app");  
			exit;
		}

		require_once 'DataObjects/Attribute.php'; 
		require_once 'DataObjects/Collection.php'; 
		require_once 'DataObjects/Collection_manager.php'; 

		$collection_name = $request->getParameterValue('collection_name');
		$ascii_id = $request->getParameterValue('ascii_id');

		if (!$collection_name || !$ascii_id) {
			$msg = "Please enter name and ascii id!";
			header("Location:$app/admin?msg=$msg");  
			exit;
		}

		$check_collection = new DataObjects_Collection;
		$check_collection->whereAdd("ascii_id = '$ascii_id' or collection_name = '$collection_name'");
		if ($check_collection->find(1)) {
			$msg = "Please choose another collection name or ascii id!";
			header("Location:$app/admin?msg=$msg");  
			exit;
		}

		$collection = new DataObjects_Collection;
		$collection->ascii_id = $ascii_id;
		$collection->collection_name = $collection_name;
		$collection->path_to_media_files = "$BASE_MEDIA_DIR/$ascii_id";
		$collection->is_public = 0;
		$coll_id = $collection->insert();
		if ($coll_id) {
			if (mkdir("$BASE_MEDIA_DIR/$ascii_id")) {
				chmod("$BASE_MEDIA_DIR/$ascii_id",0775);
				foreach ($SIZE_DIRS as $size) {
					mkdir("$BASE_MEDIA_DIR/$ascii_id/$size");
					chmod("$BASE_MEDIA_DIR/$ascii_id/$size",0775);
				}
				symlink($BASE_MEDIA_DIR.'/'.$ascii_id,$BASE_MEDIA_DIR.'/'.$ascii_id.'_collection');
			}
			foreach (array('title','description','keyword') as $att) {
				$attribute = new DataObjects_Attribute;
				$attribute->ascii_id = $att;
				$attribute->attribute_name = ucfirst($att);
				$attribute->collection_id = $coll_id;
				$attribute->insert();
			}
		}
		$cm = new DataObjects_Collection_manager;
		$cm->collection_ascii_id = $ascii_id;
		$cm->dase_user_eid = $super_admin_eid;
		$cm->auth_level = 'superuser';
		$cm->insert();

		$user->cb = $ascii_id;
		$user->last_cb_access = time();
		$user->update();

		header("Location:$app/admin/$ascii_id");
		exit;
	}

	function __get($var) {
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} else {
			return parent::__get($var);
		}
	}
}
