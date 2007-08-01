<?php

//Dase_Plugins::act('dase','before_login_form');
$tpl = Dase_Template::instance();
$tpl->assign('content','login');
$tpl->display('page.tpl');
