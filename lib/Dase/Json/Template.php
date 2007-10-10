<?php
class Dase_Json_Template 
{
	public $json;
	private $content_type_header = 'Content-Type: application/json; charset=utf-8';

	public function setJson( $json) {
		$this->json = $json;
	}

	public function display() {
		header($this->content_type_header);
		if ($this->json) {
			    echo $this->json;
		}
		exit;
	}

	public function setContentType($mime = '') {
		if ($mime) {
			$this->content_type_header = "Content-Type: $mime; charset=utf-8";
		}
	}
}
