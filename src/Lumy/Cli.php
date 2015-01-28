<?php

namespace Lumy;

use Lumy;
use Lumy\Request\Cli as Request;
use Lumy\Response\Cli as Response;
use Lumy\Router\Cli as Router;

/*
	CLI application class
*/
class Cli extends Lumy{

	/*
		Return the request object

		Return
			Lumy\Request\Cli
	*/
	protected function _getRequest(){
		return new Request;
	}

	/*
		Return the response object

		Return
			Lumy\Response\Cli
	*/
	protected function _getResponse(){
		return new Response;
	}

	/*
		Return the router object

		Return
			Lumy\Router\Cli
	*/
	protected function _getRouter(){
		return new Router;
	}

	/*
		Add a route

		Parameters
			string, array $chains   : the route pattern
			callable $controller    : a controller to call
			array $formats          : regexp list to help matching a route pattern variable
			array $defaults         : default value list for route pattern variables
			string $name            : optional route name

		Return
			Lumy
	*/
	public function route($chains,$controller,$formats=array(),$defaults=array(),$name=''){
		$chains=(array)$chains;
		foreach($chains as &$chain){
			$chain=preg_replace('/\s+/',' ',$chain);
		}
		return parent::route($chains,$controller,$formats,$defaults,$name);
	}

}
