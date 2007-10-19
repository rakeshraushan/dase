<?php
$ids = $params['ids'];
if ($ids) {
	$html = '';
	foreach (explode(',',$ids) as $id) {

		$item = new Dase_DB_Item;
		$item->load($id);
		$item->getCollection();
		$item->getValues();
		$item->getThumbnail();
		$items[] = $item;

		$item_html =<<<EOD
<div class="gridItem">
<a href="{$item->collection->ascii_id}/{$item->serial_number}">
<img src="{$item->thumbnail->url}" alt="file this in w/ simple title"/>
</a>
<h4>{$item->serial_number}</h4>
</div>
EOD;

$html .= $item_html;
}
} else {
	$html = "no items";
}
$tpl = new Dase_Html_Template;
$tpl->setText($html);
$tpl->display();
