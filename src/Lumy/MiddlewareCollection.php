<?php

namespace Lumy;

use Chernozem;
use Lumy\Exception;

/*
	Collection of middlewares
*/
class MiddlewareCollection extends Chernozem {

	/*
		Set a value

		Parameters
			mixed $key
			mixed $callback
	*/
	public function offsetSet($key, $value) {
		if(!is_callable($value)) {
			if($key) {
				throw new Exception("'$key' middleware is not callable");
			}
			else {
				throw new Exception("Provided middleware is not callable");
			}
		}
		parent::offsetSet($key, $value);
	}

	/*
		Run the middleware stack
	*/
	public function run() {
		$this->rewind();
		call_user_func($this->current(), $this);
	}

	/*
		Call the next middleware
	*/
	public function next() {
		parent::next();
		if($this->valid()) {
			return call_user_func($this->current(), $this);
		}
	}

}
