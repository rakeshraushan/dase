<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

class Dase_Atom
{

	//convenience class(es) to deal w/ Atom feeds

	private $id_is_set;
	private $rights_is_set;
	private $title_is_set;
	private $updated_is_set;
	public static $ns = array(
		'atom' => 'http://www.w3.org/2005/Atom',
		'dase' => 'http://daseproject.org/dase/',
		'dc' => 'http://purl.org/dc/elements/1.1/',
		'dcterms' => 'http://purl.org/dc/terms/',
		'opensearch' => 'http://a9.com/-/spec/opensearch/1.1/',
		'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
		'xhtml' => 'http://www.w3.org/1999/xhtml',
		'd' => 'http://daseproject.org/media/'
	);

	//convenience method for atom elements
	function addElement($tagname,$text='',$ns='') 
	{
		if (!$ns) {
			$ns = Dase_Atom::$ns['atom'];
		}
		$elem = $this->root->appendChild($this->dom->createElementNS($ns,$tagname));
		if ($text) {
			$elem->appendChild($this->dom->createTextNode($text));
		}
		return $elem;
	}

	//convenience method for atom elements
	function addChildElement($parent,$tagname,$text='',$ns='') 
	{
		if (!$ns) {
			$ns = Dase_Atom::$ns['atom'];
		}
		$elem = $parent->appendChild($this->dom->createElementNS($ns,$tagname));
		if ($text) {
			$elem->appendChild($this->dom->createTextNode($text));
		}
		return $elem;
	}

	function addAuthor($name_text='',$uri_text='',$email_text='') 
	{
		$author = $this->addElement('author');
		if (!$name_text) {
			$name_text = 'DASe (Digital Archive Services)';
			$uri_text = 'http://daseproject.org';
			$email_text = 'admin@daseproject.org';
		}
		$this->addChildElement($author,'name',$name_text);
		if ($uri_text) {
			$this->addChildElement($author,'uri',$uri_text);
		}
		if ($email_text) {
			$this->addChildElement($author,'email',$email_text);
		}
	}

	function addCategory($term,$scheme='',$label='') 
	{
		$cat = $this->addElement('category');
		$cat->setAttribute('term',$term);
		if ($scheme) {
			$cat->setAttribute('scheme',$scheme);
		}
		if ($label) {
			$cat->setAttribute('label',$label);
		}
	}

	function addContributor($name_text,$uri_text = '',$email_text = '') 
	{
		$contributor = $this->addElement('contributor');
		$this->addChildElement($contributor,'name',$name_text);
		if ($uri_text) {
			$this->addChildElement($contributor,'uri',$uri_text);
		}
		if ($email_text) {
			$this->addChildElement($contributor,'email',$email_text);
		}
	}

	function setId($text) 
	{
		if ($this->id_is_set) {
			throw new Dase_Atom_Exception('id is already set');
		} else {
			$this->id_is_set = true;
		}
		$id_element = $this->addElement('id',$text);
	}

	function addLink($href,$rel='',$type='',$length='') 
	{
		$link = $this->addElement('link');
		$link->setAttribute('href',$href);
		if ($rel) {
			$link->setAttribute('rel',$rel);
		}
		if ($type) {
			$link->setAttribute('type',$type);
		}
		if ($length) {
			$link->setAttribute('length',$length);
		}
		return $link;
	}

	function setRights($text) 
	{
		if ($this->rights_is_set) {
			throw new Dase_Atom_Exception('rights is already set');
		} else {
			$this->rights_is_set = true;
		}
		$rights = $addElement('rights',$text);
	}

	function setTitle($text) 
	{
		if ($this->title_is_set) {
			throw new Dase_Atom_Exception('title is already set');
		} else {
			$this->title_is_set = true;
		}
		$title = $this->addElement('title',$text);
	}

	function setUpdated($text) 
	{
		if ($this->updated_is_set) {
			throw new Dase_Atom_Exception('updated is already set');
		} else {
			$this->updated_is_set = true;
		}
		$updated = $this->addElement('updated',$text);
	}

	function asXml() 
	{
		//format output
		$this->dom->formatOutput = true;
		return $this->dom->saveXML();
	}


}
