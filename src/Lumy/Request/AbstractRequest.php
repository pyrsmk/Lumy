<?php

namespace Lumy\Request;

/*
	Abstract request
*/
abstract class AbstractRequest {

	/*
		Return the complete request chain

		Return
			string
	*/
	abstract public function getChain();

}
