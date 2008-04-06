<?php
class Dase_Atom_Pub_Collection extends Dase_Atom_Pub
{
	function __construct($dom,$url,$title)
	{
		$this->root = $dom->createElement('collection');
		$this->root->setAttribute('href',$url);
		$elem = $this->root->appendChild($dom->appendChild($dom->createElementNS(Dase_Atom::$ns['atom'],'atom:title')));
		//properly escapes text
		$elem->appendChild($dom->createTextNode($title));
		$this->dom = $dom;
	}

	public function addAccept($mime) {
		$this->addElement('accept',$mime);
	}
}
