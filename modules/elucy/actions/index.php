<?php

$coll = Dase_DB_Collection::get('efossils_collection');
	//$sx = new SimpleXMLElement($i->getXml());

$sx = new SimpleXMLElement($coll->getItemsByAttVal('resource_uri','/elucy/home'));


list($teacher_text) = $sx->xpath("//item/metadata[@attribute_ascii_id='text_identifier'][text()='teachers']/../metadata[@attribute_ascii_id='text']");
list($student_text) = $sx->xpath("//item/metadata[@attribute_ascii_id='text_identifier'][text()='student']/../metadata[@attribute_ascii_id='text']");
list($comp_text) = $sx->xpath("//item/metadata[@attribute_ascii_id='text_identifier'][text()='comparative']/../metadata[@attribute_ascii_id='text']");
$tpl = Dase_Template::instance('elucy');
$tpl->assign('teacher_text',$teacher_text);
$tpl->assign('student_text',$student_text);
$tpl->assign('comp_text',$comp_text);

$tpl->display('index.tpl');

