<?php

class lbInputText{
	private $name; // Declare the $name property
	private $config; // Declare the $config property

	public function lbInputText($config){
		$this->config = $config;

	}
	public function setName($name){ $this->name = $name;}
	public function render(){ return "<input type='text' name='$this->name' id='$this->name'>"; }
	public function renderLabel(){ return $this->config['label']; }
}

?>