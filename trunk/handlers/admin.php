<?php

class AdminHandler
{

	public static function getAclAsJson($params)
	{
		$acl = array();
		$cms = new Dase_DBO_CollectionManager;
		foreach ($cms->find() as $cm) {
			$acl[$cm->collection_ascii_id][$cm->dase_user_eid] = $cm->auth_level;
		}
		Dase::display(Dase_Json::get($acl));
	}

	public static function phpinfo($params)
	{
		phpinfo();
		exit;
	}

	public static function exec($params) {
		if (chmod('/mnt/www-data/dase/media/early_american_history_collection/400',0775)) {
			echo "done!";
		} else {
			echo "did not work";
		}
	}
}

