<?php

namespace Lumy;

use Lumy;
use Lumy\Exception;
use Lumy\Request\Http as Request;
use Lumy\Response\Http as Response;
use Lumy\Router\Http as Router;

/*
	HTTP application class
*/
class Http extends Lumy{

	/*
		array $__http_route_patterns     : http route patterns
		array $__http_route_replacements : http route replacements
	*/
	protected $__http_route_patterns=array(
		'%scheme%',
		'%host%',
		'%requesturi%',
		'%rooturi%',
		'%resourceuri%'
	);
	protected $__http_route_replacements;

	/*
		Constructor
	*/
	public function __construct(){
		// Init parent
		parent::__construct();
		// Add a method override middleware (for REST)
		$this->_middlewares[]=function($middlewares){
			if(isset($_POST['_METHOD'])){
				$_SERVER['REQUEST_METHOD']=strtoupper($_POST['_METHOD']);
				unset($_POST['_METHOD']);
			}
			$middlewares->next();
		};
	}

	/*
		Return the request object

		Return
			Lumy\Request\Http
	*/
	protected function _getRequest(){
		$request=new Request;
		$this->__http_route_replacements=array(
			$request->getScheme(),
			$request->getHost(),
			$request->getRequestUri(),
			$request->getRootUri(),
			$request->getResourceUri()
		);
		return $request;
	}

	/*
		Return the response object

		Return
			Lumy\Response\Http
	*/
	protected function _getResponse(){
		return new Response;
	}

	/*
		Return the router object

		Return
			Lumy\Router\Http
	*/
	protected function _getRouter(){
		return new Router;
	}

	public function route($chains,$controller,$formats=array(),$defaults=array(),$name=''){
		throw new Exception("route() method is disabled, please use get(), post(), put(), delete() or map()");
	}

	/*
		Map a route to a controller

		Parameters
			string $method          : request method
			string, array $chains   : the route pattern
			callable $controller    : a controller to call
			array $formats          : regexp list to help matching a route pattern variable
			array $defaults         : default value list for route pattern variables
			string $name            : optional route name

		Return
			Lumy
	*/
	public function map($method,$chains,$controller,$formats=array(),$defaults=array(),$name=''){
		$chains=(array)$chains;
		foreach($chains as &$chain){
			$chain=strtoupper((string)$method).'#'.
				   str_replace(
					   $this->__http_route_patterns,
					   $this->__http_route_replacements,
					   (string)$chain
				   );
		}
		return parent::route($chains,$controller,$formats,$defaults,$name);
	}

	/*
		Add a PUT route

		Parameters
			string, array $chains   : the route pattern
			callable $controller    : a controller to call
			array $formats          : regexp list to help matching a route pattern variable
			array $defaults         : default value list for route pattern variables
			string $name            : optional route name

		Return
			Lumy
	*/
	public function put($chains,$controller,$formats=array(),$defaults=array(),$name=''){
		return $this->map('PUT',$chains,$controller,$formats,$defaults,$name);
	}

	/*
		Add a GET route

		Parameters
			string, array $chains   : the route pattern
			callable $controller    : a controller to call
			array $formats          : regexp list to help matching a route pattern variable
			array $defaults         : default value list for route pattern variables
			string $name            : optional route name

		Return
			Lumy
	*/
	public function get($chains,$controller,$formats=array(),$defaults=array(),$name=''){
		return $this->map('GET',$chains,$controller,$formats,$defaults,$name);
	}

	/*
		Add a POST route

		Parameters
			string, array $chains   : the route pattern
			callable $controller    : a controller to call
			array $formats          : regexp list to help matching a route pattern variable
			array $defaults         : default value list for route pattern variables
			string $name            : optional route name

		Return
			Lumy
	*/
	public function post($chains,$controller,$formats=array(),$defaults=array(),$name=''){
		return $this->map('POST',$chains,$controller,$formats,$defaults,$name);
	}

	/*
		Add a DELETE route

		Parameters
			string, array $chains   : the route pattern
			callable $controller    : a controller to call
			array $formats          : regexp list to help matching a route pattern variable
			array $defaults         : default value list for route pattern variables
			string $name            : optional route name

		Return
			Lumy
	*/
	public function delete($chains,$controller,$formats=array(),$defaults=array(),$name=''){
		return $this->map('DELETE',$chains,$controller,$formats,$defaults,$name);
	}

	/*
		Assemble an URL from a specific route

		Parameters
			string $name
			array $arguments

		Return
			string
	*/
	public function assembleUrl($name,array $arguments){
		if(!$this->_routes[$name]){
			throw new Exception("'$name' route does not exist");
		}
		$searches=array();
		$replacements=array();
		foreach($arguments as $name=>$value){
			$searches[]="#\{$name\}#";
			$replacements[]=$value;
		}
		return preg_replace($searches,$replacements,$this->_routes[$name]->getChain());
	}

}
