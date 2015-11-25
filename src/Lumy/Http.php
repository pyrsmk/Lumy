<?php

namespace Lumy;

use Lumy;
use Lumy\Exception;
use Lumy\Request\Http as Request;
use Lumy\Response\Http as Response;
use Lumy\Router\Http as Router;
use BeatSwitch\Lock\Manager;
use BeatSwitch\Lock\Drivers\ArrayDriver;
use Lumy\Http\Path;

/*
	HTTP application class
*/
class Http extends Lumy{

	/*
		array $__http_route_patterns
		array $__http_route_replacements
		BeatSwitch\Lock\Lock $__lock
	*/
	protected $__http_route_patterns = array(
		'%scheme%',
		'%host%',
		'%requesturi%',
		'%rooturi%',
		'%resourceuri%'
	);
	protected $__http_route_replacements;
	protected $__lock;

	/*
		Constructor
	*/
	public function __construct() {
		// Prepare
		parent::__construct();
		$manager = new Manager(new ArrayDriver());
		$this->__lock = $manager->caller(new Path());
		$this->__lock->deny('all');
		// REST
		$this->_middlewares[] = function($middlewares){
			if(isset($_POST['_METHOD'])) {
				$_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_METHOD']);
				unset($_POST['_METHOD']);
			}
			$middlewares->next();
		};
		// Return allowed files
		$this->middleware(function($middlewares) {
			$path = str_replace('../', '', $this['request']->getResourceUri());
			if($this->__lock->can(explode('/', $path)) && file_exists($path)) {
				// Print file
				header('Content-Type: '.mimetype($path));
				echo file_get_contents($path);
				exit;
			}
			$middlewares->next();
		});
	}
	
	/*
		Make a path visible by clients
		
		Parameters
			string $path
		
		Return
			Lumy
	*/
	public function publish($path) {
		$this->__lock->allow(explode('/', $path));
		return $this;
	}
	
	
	/*
		Make a path invisible by clients
		
		Parameters
			string $path
		
		Return
			Lumy
	*/
	public function unpublish($path) {
		$this->__lock->deny(explode('/', $path));
		return $this;
	}

	/*
		Return the request object

		Return
			Lumy\Request\Http
	*/
	protected function _getRequest() {
		$request = new Request;
		$this->__http_route_replacements = array(
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
	protected function _getResponse() {
		return new Response;
	}

	/*
		Return the router object

		Return
			Lumy\Router\Http
	*/
	protected function _getRouter() {
		return new Router;
	}

	public function route($chains, $controller, $formats=array(), $defaults=array(), $name='') {
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
	public function map($method, $chains, $controller, $formats=array(), $defaults=array(), $name='') {
		$chains = (array)$chains;
		foreach($chains as &$chain) {
			$chain = strtoupper((string)$method).'#'.str_replace(
			   $this->__http_route_patterns,
			   $this->__http_route_replacements,
			   (string)$chain
			);
		}
		return parent::route($chains, $controller, $formats, $defaults, $name);
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
	public function put($chains, $controller, $formats=array(), $defaults=array(), $name='') {
		return $this->map('PUT', $chains, $controller, $formats, $defaults, $name);
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
	public function get($chains, $controller, $formats=array(), $defaults=array(), $name='') {
		return $this->map('GET', $chains, $controller, $formats, $defaults, $name);
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
	public function post($chains, $controller, $formats=array(), $defaults=array(), $name='') {
		return $this->map('POST', $chains, $controller, $formats, $defaults, $name);
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
	public function delete($chains, $controller, $formats=array(), $defaults=array(), $name='') {
		return $this->map('DELETE', $chains, $controller, $formats, $defaults, $name);
	}

	/*
		Assemble an URL from a specific route

		Parameters
			string $name
			array $arguments

		Return
			string
	*/
	public function assembleUrl($name, array $arguments) {
		$routes = $this->_router['routes'];
		if(!isset($routes[$name])) {
			throw new Exception("'$name' route does not exist");
		}
		$searches = array();
		$replacements = array();
		foreach($arguments as $n => $value) {
			$searches[] = "#\{$n\}#";
			$replacements[] = $value;
		}
		$route_chain = preg_replace($searches, $replacements, $routes[$name]->getChain());
		return substr($route_chain, strpos($route_chain, '#') + 1);
	}

}
