<?php

namespace Lumy\Response;

use Lumy\Http as Lumy;
use Lumy\Exception;

/*
	HTTP response object
*/
class Http extends AbstractResponse{

	/*
		array $messages : HTTP response messages
	*/
	protected $messages=array(
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',
		// Successful 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);

	/*
		Set a header

		Parameters
			string $name
			string $value
	*/
	public function setHeader($name,$value){
		if(headers_sent()){
			throw new Exception("The HTTP headers have already been sent, you cannot set a new header");
		}
		if(strpos($name,'HTTP')===0){
			header($name.' '.$value);
		}
		else{
			header($name.': '.$value);
		}
	}

	/*
		Return the requested header

		Parameters
			string $name

		Return
			string, false
	*/
	public function getHeader($name){
		foreach(headers_list() as $header){
			if(strpos($header,$name)===0){
				preg_match('/: (.+)$/',$header,$match);
				return $match[1];
			}
		}
		return false;
	}

	/*
		Unset a header

		Parameters
			string $name
	*/
	public function unsetHeader($name){
		header_remove($name);
	}

	/*
		Set the HTTP status to send

		Parameters
			integer $code
			string $message

		Return
			Lumy\Environment\Http

	*/
	public function setStatus($code){
		$code=(int)$code;
		$this->setHeader(($_SERVER['SERVER_PROTOCOL']?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.0'),$code.' '.$this->messages[$code]);
		$this->setHeader('Status',$code.' '.$this->messages[$code]);
		return $this;
	}
	
	/*
		Return the HTTP status
		
		Return
			integer
	*/
	public function getStatus(){
		if($status=$this->getHeader('Status')){
			preg_match('/^\d{3}/',$status,$match);
			return (int)$match[0];
		}
		else{
			return 200;
		}
	}

	/*
		Redirect to a new URL
		
		Parameters
			string $url     : the URL
			integer $status : a HTTP status code
	*/
	public function redirect($url,$status=302){
		$this->setStatus($status);
		if($url{0}=='/'){
			$lumy=Lumy::getInstance();
			$url=$lumy['request']->getRootUri().$url;
		}
		$this->setHeader('Location',$url);
		exit;
	}

	/*
		Send a file to the client

		Parameters
			string $path : file to send

		Return
			Lumy\Environment\Http
	*/
	public function send($path){
		$this->_printToBrowser($path,'attachment');
		return $this;
	}

	/*
		Display a file directly in the page

		Parameters
			string $path : file to display

		Return
			Lumy\Environment\Http
	*/
	public function display($path){
		$this->_printToBrowser($path,'inline');
		return $this;
	}

	/*
		Print a file to the browser

		Parameters
			string $path		: file to print
			string $disposition	: file disposition (inline or attachment)
	*/
	protected function _printToBrowser($path,$disposition){
		// Verify
		if(!file_exists($path)){
			throw new Exception("'$path' file does not exist");
		}
		// Get file contents
		$contents=file_get_contents($path);
		// Guess mime type
		$finfo=finfo_open(FILEINFO_MIME);
		$mimetype=finfo_file($finfo,$path);
		finfo_close($finfo);
		// Set headers
		$this->setHeader('Content-Type',$mimetype);
		$this->setHeader('Content-Length',strlen($contents));
		$this->setHeader('Content-Disposition',$disposition.'; filename="'.basename($path).'"');
		// Print contents
		echo $contents;
		exit;
	}

}
