<?php

namespace Lumy\Request;

/*
	CLI request object
*/
class Cli extends AbstractRequest{

	/*
		Return the complete request chain

		Return
			string
	*/
	public function getChain(){
		return implode(' ',$this->getArguments());
	}

	/*
		Return the application name

		Return
			string
	*/
	public function getApplicationName(){
		$args=(array)$_SERVER['argv'];
		return $args[0];
	}

	/*
		Return application arguments

		Return
			array
	*/
	public function getArguments(){
		$args=(array)$_SERVER['argv'];
		array_shift($args);
		return $args;
	}

}
