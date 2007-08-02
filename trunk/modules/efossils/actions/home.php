<?php

$tpl = Dase_Template::instance('efossils');
$tpl->assign('msg','hello world');
$tpl->display('index.tpl');
exit;

