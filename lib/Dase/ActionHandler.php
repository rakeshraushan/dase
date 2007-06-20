<?php

Abstract Class Dase_Controller 
{

	protected $registry;

	function __construct($registry) {
		$this->registry = $registry;
	}

	abstract function index();
}

