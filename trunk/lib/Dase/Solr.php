<?php
Class Dase_Solr 
{
	protected $url;
	protected $db;

	function __construct($db,$url) 
	{
		$this->url = $url;
		$this->db = $db;
	}

	public function getLookup($lookup_id='dase')
	{
		//serialized for solr
		$dom = new DOMDocument();
		$root_el = $dom->createElement('add');
		$root = $dom->appendChild($root_el);
		$doc_el = $dom->createElement('doc');
		$doc = $root->appendChild($doc_el);
		$id = $doc->appendChild($dom->createElement('field'));
		$id->appendChild($dom->createTextNode($lookup_id));
		$id->setAttribute('name','id');
		$colls = new Dase_DBO_Collection($this->db);
		foreach ($colls->find() as $c) {
			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode($c->collection_name));
			$field->setAttribute('name',$c->ascii_id);
		}
		$atts = new Dase_DBO_Attribute($this->db);
		foreach ($atts->find() as $a) {
			$a = clone($a);
			if  (0 == $a->collection_id) {
				$coll = 'admin';
			} else {
				$coll = $a->getCollection()->ascii_id;
			}
			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode($a->attribute_name));
			$field->setAttribute('name',$coll.':'.$a->ascii_id);
		}
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	public function initSolrDase()
	{
		$lookup = $this->getLookup();
		$msg = Dase_Http::post($this->url,$lookup,null,null,'text/xml');
		$this->commit();
		return $msg;
	}

	public function commit()
	{
		return Dase_Http::post($this->url,'<commit/>',null,null,'text/xml');
	}

}


