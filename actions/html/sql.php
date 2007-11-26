<?php

$search = Dase_Search::get($params);
$result = $search->getResult();
print "<pre>{$result['sql']}</pre>";
exit;


