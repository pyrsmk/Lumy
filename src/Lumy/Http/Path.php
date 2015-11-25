<?php

namespace Lumy\Http;

use BeatSwitch\Lock\Callers\Caller;

class Path implements Caller {
	
	public function getCallerType() {
		return 'paths';
	}
	
	public function getCallerId() {
		return 'path';
	}
	
	public function getCallerRoles() {
		return array();
	}
	
}