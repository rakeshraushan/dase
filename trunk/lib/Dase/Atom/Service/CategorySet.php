<?php
class Dase_Atom_Service_CategorySet extends Dase_Atom_Service
{
	function __construct($dom,$fixed='yes',$scheme='',$href='')
	{
		$this->root = $dom->createElement('categories');
		if ($href) {
			$this->root->setAttribute('href',$url);
			return;
		}
		if ($fixed) {
			$this->root->setAttribute('fixed',$fixed);
		}
		if ($scheme) {
			$this->root->setAttribute('scheme',$scheme);
		}
		$this->dom = $dom;
	}

	function addCategory($term,$scheme='',$label='') 
	{
		$cat = $this->addElement('category',null,Dase_Atom::$ns['atom']);
		$cat->setAttribute('term',$term);
		if ($scheme) {
			$cat->setAttribute('scheme',$scheme);
		}
		if ($label) {
			$cat->setAttribute('label',$label);
		}
	}

}
