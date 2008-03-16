<?php

class Dase_Test
{
	private $succeeded = 0;
	private $failed = 0;
	private $sx;
	public $results_xml;

	//might need to reimplement this as a singleton
	//how about as an Atom feed???????
	public function __construct()
	{
		$this->sx = simplexml_load_string('<tests/>');
	}

	function assertTrue($test,$name)
	{
		$test_xml = $this->sx->addChild('test');
		$test_xml->addAttribute('name',$name);
		if ($test) {
			$test_xml->addAttribute('result','success');
			$this->succeeded++;
		} else {
			$test_xml->addAttribute('result','failed');
			$this->failed++;
		}	
	}

	function asXml()
	{
		return $this->asSimpleXml()->asXml();
	}

	function asSimpleXml()
	{
		$result = $this->sx->addChild('result');
		$result->addChild('failed',$this->failed);
		$result->addChild('succeeded',$this->succeeded);
		$result->addChild('total',$this->succeeded+$this->failed);
		return $this->sx;
	}

	//todo: work on text-only tests also
}

