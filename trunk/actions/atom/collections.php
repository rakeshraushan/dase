<?php

if (Dase::filterGet('get_all')) {
	$public_only = false;
} else {
	$public_only = true;
}

Dase::display(Dase_DB_Collection::listAsAtom($public_only));

