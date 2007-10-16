<?php
class Dase_Html_Template 
{
	public $text;
	private $content_type_header = 'Content-Type: text/html; charset=utf-8';

	public function setText( $text) {
		$this->text = $text;
	}

	public function display() {
		header($this->content_type_header);
		if ($this->text) {
			    echo $this->text;
		}
		exit;
	}

	//could create a cache method here

	public function setContentType($mime = '') {
		if ($mime) {
			$this->content_type_header = "Content-Type: $mime; charset=utf-8";
		}
	}
}
