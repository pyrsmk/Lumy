<?php

namespace Lumy\Router;

use Chernozem;

/*
	Abstract router
*/
abstract class AbstractRouter extends Chernozem {

	protected $_routes = array();
	
	/*
		Add a route

		Parameters
			string $name
			Lumy\Router\Route $route
	*/
	public function addRoute($name, Route $route) {
		if(!($route instanceof Route)) {
			if(empty($name)) {
				throw new Exception("Provided route's value must be a Lumy\Route object");
			}
			else {
				throw new Exception("'$name' route's value must be a Lumy\Route object");
			}
		}
		if(empty($name)) {
			$this->_routes[] = $route;
		}
		else {
			$this->_routes[$name] = $route;
		}
	}

	/*
		Route the request to the right controller
	*/
	public function route() {
		$controller = $this->_getController();
		if(!$controller) {
			throw new NotFound("No matching controller found for this request");
		}
		$controller();
	}

	/*
		Return the controller corresponding to the request
		
		Return
			Closure, false
	*/
	abstract protected function _getController();

}
