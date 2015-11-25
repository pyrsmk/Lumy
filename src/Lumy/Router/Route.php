<?php

namespace Lumy\Router;

use LongueVue;

/*
	A route
*/
class Route {

	/*
		string $chain
		LongueVue $matcher
		callable $controller
	*/
	protected $chain;
	protected $matcher;
	protected $controller;

	/*
		Constructor

		Parameters
			string $route           : the route pattern
			array $formats          : regexp list to help matching a route pattern variable
			array $defaults         : default value list for route pattern variables
			callable $controller    : a controller to call
	*/
	public function __construct($chain, $formats, $defaults, $controller) {
		// Normalize
		$chain = (string)$chain;
		$formats = (array)$formats;
		$defaults = (array)$defaults;
		// Prepare formats
		preg_match_all('#\{(\w+)\}#S',$chain,$matches);
		foreach($matches[1] as $match) {
			$format = &$formats[$match];
			if(!$format) {
				$format = '\w+';
			}
		}
		// Verify controller
		if(!is_callable($controller)) {
			throw new Exception("The controller for the '$chain' route is not callable");
		}
		// Save data
		$this->chain = $chain;
		$this->matcher = new LongueVue($chain,$formats,$defaults);
		foreach($formats as $slug => $regex) {
			$this->matcher->addValidator($slug, $regex);
		}
		foreach($defaults as $slug => $regex) {
			$this->matcher->addDefaultValue($slug, $regex);
		}
		$this->controller = $controller;
	}

	/*
		Match a chain

		Return
			mixed
	*/
	public function match($chain) {
		return $this->matcher->match($chain);
	}

	/*
		Get the route chain

		Return
			string
	*/
	public function getChain() {
		return $this->chain;
	}

	/*
		Get the matcher object

		Return
			LongueVue
	*/
	public function getMatcher() {
		return $this->matcher;
	}

	/*
		Get the controller

		Return
			callable
	*/
	public function getController() {
		return $this->controller;
	}

}
