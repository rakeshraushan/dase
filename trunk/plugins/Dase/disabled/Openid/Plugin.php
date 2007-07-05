<?php

class Dase_Openid_Plugin extends Dase_Plugin
{
	public function load() {
	}

	public function beforeDisplay() {
#		Dase_Template::instance()->assign('msg','Dase_Eid_Plugin loaded!');
	}

	public function beforeLoginForm() {
		$tpl = Dase_Template::instance();
		$tpl->assign('content','plugin');
		$tpl->assign_plugin_template('Dase/Openid/login.tpl');
		$tpl->display('page.tpl');

	}

	public function moduleFilter() {
		$params = func_get_args();
		if (isset($params[0])) {
			$string = $params[0];
			if ('openid' == $string) {
				$string = 'openid_';
			}
			return $string;
		}
	}

	public function beforeLogoff() {
		setcookie('DOC','',time()-86400,'/','.utexas.edu');
		setcookie('FC','',time()-86400,'/','.utexas.edu');
		setcookie('SC','',time()-86400,'/','.utexas.edu');
		setcookie('TF','',time()-86400,'/','.utexas.edu');
	}
}

