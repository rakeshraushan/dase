<?php
class Dase_Template 
{
	private static $template;
	private static $instance;

	private function __construct() {}

	//singleton
	public static function instance() {
		if (empty(self::$template)) {
			self::$template = new Smarty;
			self::$template->assign('app_root',APP_ROOT);
			self::$template->assign('app_http_root',APP_HTTP_ROOT);
			self::$template->assign('app_https_root',APP_HTTPS_ROOT);
			self::$template->assign('user',Dase::getUser());
			self::$template->assign('title','Digital Archive SErvices');
			self::$template->assign('msg',filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_STRING));
			self::$instance = new Dase_Template();
		}
		return self::$instance;
	}

	public function assign( $key, $value) {
		self::$template->assign($key,$value);
	}

	public function assign_plugin_template( $pt_path ) {
		if (strstr($pt_path,'..')) {
			return;
		}
		$full_path = DASE_PATH . '/plugins/' . $pt_path;
		self::$template->assign('plugin_template',$full_path);
	}

	public function display( $template = 'page.tpl' ) {
		//NOTE: code MUST use template class to guarantee session data will be saved
		Dase_Session::write();
		self::$template->assign('timer',Dase_Timer::getElapsed());
		self::$template->display( $template );
		exit;
	}
}
