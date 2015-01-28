<?php

namespace Lumy\Router;

use Lumy;

/*
	HTTP router
*/
class Http extends AbstractRouter{

	/*
		Return the controller corresponding to the request
		
		Return
			Closure, false
	*/
	protected function _getController(){
		// Get request object
		$lumy=Lumy\Http::getInstance();
		$request=$lumy['request'];
		// Get request method
		$method=$request->getMethod();
		// Search
		$arguments=false;
		foreach($this->__chernozem_values as $i=>$route){
			// Just match the request chain when its method is valid
			if(strpos($route->getChain(),$method)!==0){
				continue;
			}
			// Get the HTTP request chain
			else{
				$route_chain=substr($route->getChain(),strpos($route->getChain(),'#')+1);
				// By Scheme/Host/RequestURI
				if(strpos($route_chain,'http:')===0 || strpos($route_chain,'https:')===0){
					$chain=$request->getChain();
				}
				// By Host/RequestURI
				elseif(strpos($route_chain,'//')===0){
					$chain='//'.$request->getHost().$request->getRequestUri();
				}
				// By RequestURI
				elseif($request->getRootUri() && strpos($route_chain,$request->getRootUri())===0){
					$chain=$request->getRequestUri();
				}
				// By ResourceURI
				else{
					$chain=$request->getResourceUri();
				}
			}
			// Is this the good route?
			if(($arguments=$route->match($method.'#'.$chain))!==false){
				break;
			}
		}
		// No controller found
		if($arguments===false){
			return false;
		}
		// Controller found
		return function() use($route,$arguments){
			return call_user_func_array($route->getController(),$arguments);
		};
	}

}
