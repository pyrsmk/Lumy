<?php

namespace Lumy\Router;

use Lumy\Cli as Lumy;

/*
	CLI router
*/
class Cli extends AbstractRouter {

	/*
		Return the controller corresponding to the request
		
		Return
			Closure, false
	*/
	protected function _getController() {
		// Prepare
		$arguments = false;
		$lumy = Lumy::getInstance();
		$chain = $lumy['request']->getChain();
		// Search
		foreach($this->_routes as $route) {
			if(($arguments = $route->match($chain)) !== false){
				break;
			}
		}
		// No controller found
		if($arguments === false) {
			return false;
		}
		// Controller found
		return function() use($route,$arguments) {
			return call_user_func_array($route->getController(), $arguments);
		};
	}

}
