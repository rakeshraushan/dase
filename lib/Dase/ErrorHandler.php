<?php

class Dase_ErrorHandler 
{
	public static function index() {
		$tpl = Dase_Template::instance();
		$tpl->assign('content','error');
		$tpl->display();
		exit;
	}

}
