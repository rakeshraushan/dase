<?php

class Dase_Xhtml_Item extends Dase_Xhtml
{
	private $_metadata;
	private $_media;

	function __construct(DOMDocument $dom,$serial_number,$is_sub=false)
	{
		$this->dom = $dom;
		$this->root = $dom->createElementNS(Dase_Xhtml::$ns['h'],'div');
		if ($is_sub) {
			$this->root->setAttribute('class','subitem');
		} else {
			$this->root->setAttribute('class','item');
		}
		$this->root->setAttribute('id',$serial_number);
		$this->addChildElement($this->root,'h2',$serial_number);
	}

	public function setItemType($ascii_id,$name)
	{
		$h3 = $this->addChildElement($this->root,'h3',$name);
		$h3->setAttribute('class',$ascii_id);
	}

	public function addMetadata($att_ascii_id,$att_name,$value_text)
	{
		if (!$this->_metadata) {
			$this->_metadata = $this->addChildElement($this->root,'dl');
			$this->_metadata->setAttribute('class','metadata');
		}
		$dt = $this->addChildElement($this->_metadata,'dt',$att_name);
		$dd = $this->addChildElement($this->_metadata,'dd',$value_text);
		$dd->setAttribute('class',$att_ascii_id); 
	}

	public function addSubItem($serial_number)
	{
		$subitem = new Dase_Xhtml_Item($this->dom,$serial_number,true);
		//items will be appended in asXml method
		$this->root->appendChild($subitem->root);
		return $subitem;
	}

	public function addMediaFile($mime_type,$filename)
	{
		if (!$this->_media) {
			$this->_media = $this->addChildElement($this->root,'ul');
			$this->_media->setAttribute('class','media');
		}
		$li = $this->addChildElement($this->_media,'li',$filename);
		$li->setAttribute('class',$mime_type); 
	}
}
