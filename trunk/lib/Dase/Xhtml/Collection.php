<?php

/********* minmal *************
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Minimal XHTML 1.0 Document</title>
	</head>
	<body>
		<p>This is a minimal <a href="http://www.w3.org/TR/xhtml1/">XHTML 1.0</a> 
		document.</p>
	</body>
</html>
********************/

/******* style
 *
 *

 <style type="text/css">
 body {
	 font-size: x-small;
 }
div.item {
	margin: 2em;
	background-color: #eee;
	padding: 10px;
}

div.subitem {
	margin: 1em;
	background-color: #ffc;
	padding: 10px;
}


*
 *
 * ********************/

class Dase_Xhtml_Collection extends Dase_Xhtml 
{
	public $body;
	private $_collection_is_set;
	private $_items = array();

	function __construct($dom = null)
	{
		if ($dom) {
			//reader object
			$this->root = $dom;
			$this->dom = $dom;
		}  else {
			//creator object
			$dom = new DOMDocument('1.0','utf-8');
			$this->root = $dom->appendChild($dom->createElementNS(Dase_Xhtml::$ns['h'],'html'));
			$this->dom = $dom;
		}
	}

	function setName($ascii_id,$name) 
	{
		if ($this->_collection_is_set) {
			return;
		}
		$head = $this->addElement('head');
		$this->addChildElement($head,'title',$name);
		$body = $this->addElement('body');
		$h1 = $this->addChildElement($body,'h1',$name);
		$h1->setAttribute('class','collection');
		$h1->setAttribute('id',$ascii_id);
		$this->body = $body;
	}

	function addItem($serial_number)
	{
		$item = new Dase_Xhtml_Item($this->dom,$serial_number);
		//items will be appended in asXml method
		$this->_items[] = $item;
		return $item;
	}

	function attachItems()
	{
		if ($this->_items) {
			foreach ($this->_items as $item) {
				$this->root->appendChild($item->root);
			}
		}
	}

	function asXml()
	{
		$this->attachItems();
		return parent::asXml();
	}
}
