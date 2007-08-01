<?php

Class Dase_Eid_Handler
{
	private function __construct() {}

	public static function index() {
		echo "hello from the dase_eid plugin"; exit;
	}
	public static function lookup() {
		$query = Dase_Utils::filterGet('query');
		$type = Dase_Utils::filterGet('type');
		$tpl = Dase_Template::instance();
		$tpl->assign('person_array',Dase_Eid_Ldap::lookup($query,$type));
		$tpl->assign('content','plugin');
		$tpl->assign_plugin_template('Dase/Eid/lookup.tpl');
		Dase_Plugins::act('dase','before_display');
		$tpl->display('page.tpl');
	}
}

