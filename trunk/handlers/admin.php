<?php

class AdminHandler
{

	public static function monitor($params) {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'admin/index.xsl';
		Dase::display($t->transform());
	}

	public static function calendar($params) {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'admin/calendar.xsl';
		$t->source = XSLT_PATH.'admin/calendar.xml';
		Dase::display($t->transform());
	}

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

	public static function smarty($params)
	{
		$tpl = new Dase_Template();
		$tpl->atomDoc('item',DASE_URL.'/atom/collection/vrc/search?q=io&num=1');
		Dase::display($tpl->fetch('item/transform.tpl'));
	}

	public static function exec($params) {
		if (chmod('/mnt/www-data/dase/media/early_american_history_collection/400',0775)) {
			echo "done!";
		} else {
			echo "did not work";
		}
	}
}

