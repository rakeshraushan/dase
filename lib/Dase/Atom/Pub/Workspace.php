<?php
class Dase_Atom_Pub_Workspace extends Dase_Atom_Pub
{
	function __construct($dom,$title)
	{
		$this->root = $dom->createElement('workspace');
		$elem = $this->root->appendChild($dom->appendChild($dom->createElementNS(Dase_Atom::$ns['atom'],'atom:title')));
		$elem->appendChild($dom->createTextNode($title));
		$this->dom = $dom;
	}

	function addCollection($url,$title)
	{
		$collection = new Dase_Atom_Pub_Collection($this->dom,$url,$title);
		$this->root->appendChild($collection->root);
		return $collection;
	}


}
