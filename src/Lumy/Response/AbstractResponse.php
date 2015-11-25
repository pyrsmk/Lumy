<?php

namespace Lumy\Response;

/*
	Abstract response
*/
abstract class AbstractResponse {

	/*
		string $body
	*/
	protected $body = '';

	/*
		Set the response body

		Parameters
			string $body
	*/
	public function setBody($body) {
		$this->body = (string)$body;
	}

	/*
		Prepend content to the response body

		Parameters
			string $body
	*/
	public function prependBody($body) {
		$this->body = ((string)$body).$this->body;
	}

	/*
		Append content to the response body

		Parameters
			string $body
	*/
	public function appendBody($body) {
		$this->body .= (string)$body;
	}

	/*
		Get the response body

		Return
			string
	*/
	public function getBody() {
		return $this->body;
	}

	/*
		Print the response body

		Return
			string
	*/
	public function __toString() {
		return $this->getBody();
	}

}
