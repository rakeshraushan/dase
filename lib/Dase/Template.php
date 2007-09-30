<?php
class Dase_Template 
{
	private static $template;
	private static $instance;

	private function __construct() {}

	//singleton
	public static function instance($module = null) {
		if (empty(self::$template)) {
			self::$template = new Smarty;
			if ($module) {
				//to prevent template files with same names from trouncing 
				//each other in shared compile dir:
				self::$template->compile_id = $module;
				self::$template->template_dir = "modules/$module/templates";
				self::$template->assign('module_root',APP_ROOT . "/modules/$module");
			}
			self::$template->assign('app_root',APP_ROOT);
			self::$template->assign('app_http_root',APP_HTTP_ROOT);
			self::$template->assign('app_https_root',APP_HTTPS_ROOT);
			$user = Dase::getUser();
			if ($user) {
				self::$template->assign('user',$user);
				if (in_array($user->eid,Dase::getConf('superuser'))) {
					self::$template->assign('superuser',1);
				}
			}
			self::$template->assign('title','DASe@' . $_SERVER['HTTP_HOST']);
			self::$template->assign('msg',Dase::filterGet('msg'));
			self::$instance = new Dase_Template();
		}
		return self::$instance;
	}

	public function assign( $key, $value) {
		self::$template->assign($key,$value);
	}

	public function display( $template = 'page.tpl' ) {
		//NOTE: code MUST use template class to guarantee session data will be saved
		if (!defined('NO_SESSIONS')) {
			Dase_Session::write();
		}
		self::$template->assign('timer',Dase_Timer::getElapsed());
		self::$template->display( $template );
		exit;
	}
}
