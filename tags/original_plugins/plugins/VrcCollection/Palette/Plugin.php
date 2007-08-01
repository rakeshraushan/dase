<?php

class VrcCollection_Palette_Plugin 
{
	public function load() {
		echo "greetings from the vrc!";
	}

	public function beforeDisplay() {
		Dase_Template::instance()->assign('msg','Hey you have loaded a plugin!');
	}
}

