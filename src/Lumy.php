<?php

use Lumy\MiddlewareCollection;
use Lumy\Router\Route;
use Lumy\Exception;

/*
	Abstract application base class
*/
abstract class Lumy extends Chernozem {

	/*
		Lumy $instance								: application instance
		Lumy\Request\AbstractRequest $_request		: the request object
		Lumy\Response\AbstractResponse $_request	: the response object
		Lumy\Router\AbstractRouter $_router			: the router
		Lumy\MiddlewareCollection  $_middlewares	: middleware collection
		callable $__error							: error controller
	*/
	static protected $instance;
	protected $_request;
	protected $_response;
	protected $_router;
	protected $_middlewares;
	protected $__error;

	/*
		Constructor
	*/
	public function __construct(){
		// Init core objects
		$this->_request = $this->_getRequest();
		$this->_response = $this->_getResponse();
		$this->_router = $this->_getRouter();
		$this->_middlewares = new MiddlewareCollection;
		// Init error closure
		$this->__error = function($exception) {
			throw $exception;
		};
		// Save instance
		self::$instance = $this;
	}

	/*
		Return the request object

		Return
			Lumy\Environment\AbstractRequest
	*/
	abstract protected function _getRequest();

	/*
		Return the response object

		Return
			Lumy\Environment\AbstractResponse
	*/
	abstract protected function _getResponse();

	/*
		Return the router object

		Return
			Lumy\Router\RouterInterface
	*/
	abstract protected function _getRouter();

	/*
		Get the application instance

		Return
			Lumy
	*/
	static public function getInstance() {
		if(!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
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
	public function route($chains, $controller, $formats=array(), $defaults=array(), $name=null) {
		$chains = (array)$chains;
		foreach($chains as $chain) {
			$this->_router->addRoute($name, new Route($chain, $formats, $defaults, $controller));
		}
		return $this;
	}

	/*
		Register a controller to call when an error occurs

		Parameters
			callable $controller

		Return
			Lumy
	*/
	public function error($controller) {
		if(!is_callable($controller)) {
			throw new Exception("The provided error controller is not callable");
		}
		$this->__error = $controller;
		return $this;
	}

	/*
		Add a middleware

		Parameters
			callable $callback
			string $name

		Return
			Lumy
	*/
	public function middleware($callback, $name = null) {
		if($name) {
			$this->_middlewares[(string)$name] = $callback;
		}
		else {
			$this->_middlewares[] = $callback;
		}
		return $this;
	}

	/*
		Run the application

		Return
			Lumy\Response\AbstractResponse
	*/
	public function run() {
		try {
			// Add the routing mechanism to the middleware stack
			$router = $this->_router;
			$this->_middlewares['routing'] = function($middlewares) use($router) {
				$router->route();
				$middlewares->next();
			};
			// Run the middleware collection
			$this->_middlewares->run();
			// Remove the routing mechanism
			unset($this->_middlewares['routing']);
		}
		// Catch errors
		catch(\Exception $e) {
			// Remove the routing mechanism
			unset($this->_middlewares['routing']);
			// Handle exception
			$error = $this->__error;
			$error($e);
		}
		return $this->_response;
	}

}
