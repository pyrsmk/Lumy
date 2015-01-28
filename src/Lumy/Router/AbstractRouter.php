<?php

namespace Lumy\Router;

use Chernozem;

/*
	Abstract router
*/
abstract class AbstractRouter extends Chernozem{

	/*
		Set a route

		Parameters
			string $name
			Lumy\Router\Route $route
	*/
	public function offsetSet($name,$route){
		if(!($route instanceof Route)){
			if($name){
				throw new Exception("'$name' route's value must be a Lumy\Route object");
			}
			else{
				throw new Exception("Provided route's value must be a Lumy\Route object");
			}
		}
		parent::offsetSet($name,$route);
	}

	/*
		Route the request to the right controller
	*/
	public function route(){
		$controller=$this->_getController();
		if(!$controller){
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
