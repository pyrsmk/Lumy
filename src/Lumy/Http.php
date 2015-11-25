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
		$this->middleware(function($middlewares){
			if(isset($_POST['_METHOD'])) {
				$_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_METHOD']);
				unset($_POST['_METHOD']);
			}
			$middlewares->next();
		});
		// Return allowed files
		$this->middleware(function($middlewares) {
			$path = $this->_formatPath($this['request']->getResourceUri());
			if(
				file_exists($path) &&
				is_file($path) &&
				$this->__lock->can($this->_formatPath($path))
			) {
				// Get mime type
				$mime = mimetype($path);
				// Try some more extensions
				if($mime == 'text/plain') {
					if(strpos($path, '.css')) $mime = 'text/css';
					if(strpos($path, '.js')) $mime = 'application/javascript';
					if(strpos($path, '.json')) $mime = 'application/json';
				}
				// Set content type
				if($mime != 'text/plain') {
					header('Content-Type: '.$mime);
				}
				// Print file
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
		$path = $this->_formatPath($path);
		if(is_dir($path)) {
			foreach(lessdir($path) as $file) {
				$this->publish($path.'/'.$file);
			}
		}
		else {
			$this->__lock->allow($path);
		}
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
		$path = $this->_formatPath($path);
		if(is_dir($path)) {
			foreach(lessdir($path) as $file) {
				$this->unpublish($path.'/'.$file);
			}
		}
		else {
			$this->__lock->deny($path);
		}
		return $this;
	}
	
	/*
		Format a path for publishing routines
		
		Parameters
			string $path
		
		Return
			string
	*/
	protected function _formatPath($path) {
		$path = str_replace('../', '', $path);
		$path = $path[0] == '/' ? substr($path, 1) : $path;
		return $path;
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
