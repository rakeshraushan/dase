<?php

$tpl = Dase_Template::instance('efossils');
$tpl->assign('msg','hello world');
$tpl->display('hello.tpl');
exit;

