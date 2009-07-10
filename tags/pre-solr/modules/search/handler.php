<?php

function unhtmlspecialchars( $string )
{
	$string = str_replace ( '&amp;', '&', $string );
	$string = str_replace ( '&#039;', '\'', $string );
	$string = str_replace ( '&quot;', '"', $string );
	$string = str_replace ( '&lt;', '<', $string );
	$string = str_replace ( '&gt;', '>', $string );
	return $string;
}

class Dase_ModuleHandler_Search extends Dase_Handler 
{
	public $resource_map = array(
		'index' => 'index',
		'search' => 'search',
	);

	public function setup($r)
	{
	}

	public function getIndex($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('home.tpl'));
	}

	public function getSearch($r) 
	{
		$s = new Solr($r);
		$t = new Dase_Template($r,true);
		$t->assign('q',$r->get('q'));
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($s->getResults());
		$atom = '';
		foreach ($dom->getElementsByTagName('arr') as $el) {
			if ('atom' == $el->getAttribute('name')) {
				foreach ($el->getElementsByTagName('str') as $at_el) {
					$atom .= unhtmlspecialchars($at_el->nodeValue);
				}
			}
		}
		$t->assign('results',$atom);
		$r->renderResponse($t->fetch('home.tpl'));
	}

	public function getSearchXml($r) 
	{
		$s = new Solr($r);
		$r->renderResponse($s->getResults());
	}

	public function getSearchAtom($r) 
	{
		$s = new Solr($r);
		$r->renderResponse($s->getResultsAsAtom());
	}
}
