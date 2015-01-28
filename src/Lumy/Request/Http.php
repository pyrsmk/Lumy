<?php

namespace Lumy\Request;

use Lumy\Exception;

/*
	HTTP request object
*/
class Http extends AbstractRequest{

	/*
		string HTTP_SCHEME  : HTTP protocol
		string HTTPS_SCHEME : HTTPS protocol
	*/
	const HTTP_SCHEME   = 'http';
	const HTTPS_SCHEME  = 'https';

	/*
		string $scheme          : the request's scheme
		string $host            : the host
		integer $port           : the port
		string $request_uri     : the request URI
		string $root_uri        : the root URI
		string $resource_uri    : the resource URI
		string $method          : the used HTTP request method
		boolean $ajax           : true if it's an ajax request
		boolean $flash          : true if it's a flash request
		boolean $secure         : true if the request is secured
		string $ip              : the client IP
	*/
	protected $scheme;
	protected $host;
	protected $port;
	protected $request_uri;
	protected $root_uri;
	protected $resource_uri;
	protected $method;
	protected $ajax;
	protected $flash;
	protected $secure;
	protected $ip;

	/*
		Return the complete request chain

		Return
			string
	*/
	public function getChain(){
		return $this->getScheme().'://'.
			   $this->getHost().
			   $this->getRootUri().
			   $this->getResourceUri();
	}

	/*
		Return the request's scheme

		Return
			string
	*/
	public function getScheme(){
		if(!$this->scheme){
			if($this->isSecure()){
				$this->scheme=self::HTTPS_SCHEME;
			}
			else{
				$this->scheme=self::HTTP_SCHEME;
			}
		}
		return $this->scheme;
	}

	/*
		Return the host

		Return
			string
	*/
	public function getHost(){
		if(!$this->host){
			// HTTP_HOST test
			$this->host=$_SERVER['HTTP_HOST'];
			// SERVER_NAME test
			if(!$this->host){
				$this->host=$_SERVER['SERVER_NAME'];
			}
		}
		return $this->host;
	}
	
	/*
		Return the port

		Return
			integer
	*/
	public function getPort(){
		if(!$this->port){
			$this->port=(int)$_SERVER['SERVER_PORT'];
		}
		return $this->port;
	}

	/*
		Get the request URI

		Return
			string
	*/
	public function getRequestUri(){
		if(!$this->request_uri){
			// Verify IIS first
			if($_SERVER['HTTP_X_REWRITE_URL']){
				$this->request_uri=$_SERVER['HTTP_X_REWRITE_URL'];
			}
			// IIS 7 + rewriting: just valid with non encoded URLs because of a double slash issue
			elseif($_SERVER['IIS_WasUrlRewritten']=='1' && $_SERVER['UNENCODED_URL']){
				$this->request_uri=$_SERVER['UNENCODED_URL'];
			}
			// REQUEST_URI
			elseif($_SERVER['REQUEST_URI']){
				$this->request_uri=$_SERVER['REQUEST_URI'];
				// Remove hostname
				$full_host=$this->getScheme().'://'.$this->getHost();
				if(strpos($this->request_uri,$full_host)===0){
					$this->request_uri=substr($this->request_uri,strlen($full_host));
				}
			}
			// IIS 5.0, PHP CGI
			elseif($_SERVER['ORIG_PATH_INFO']){
				$this->request_uri=$_SERVER['ORIG_PATH_INFO'];
			}
			// Clean up
			if($pos=strpos($this->request_uri,'?')){
				$this->request_uri=substr($this->request_uri,0,$pos);
			}
		}
		return $this->request_uri;
	}

	/*
		Get the root URI

		Return
			string
	*/
	public function getRootUri(){
		if($this->root_uri===null){
			preg_match('/^(.+?)\/[^\/]+$/',$_SERVER['PHP_SELF'],$match);
			$this->root_uri=isset($match[1])?$match[1]:'';
		}
		return $this->root_uri;
	}

	/*
		Get the resource URI

		Return
			string
	*/
	public function getResourceUri(){
		if(!$this->resource_uri){
			$this->resource_uri=substr($this->getRequestUri(),strlen($this->getRootUri()));
		}
		return $this->resource_uri;
	}

	/*
		Get the request method

		Return
			string
	*/
	public function getMethod(){
		if(!$this->method){
			$this->method=$_SERVER['REQUEST_METHOD'];
		}
		return $this->method;
	}

	/*
		Get a request header

		Return
			string
	*/
	public function getHeader($name){
		$name=str_replace('-','_',strtoupper($name));
		$header=$_SERVER[$name];
		if(!$header){
			$header=$_SERVER['HTTP_'.$name];
		}
		return $header;
	}

	/*
		Verify if the request is an XmlHttpRequest

	Return
			boolean
	*/
	public function isAjax(){
		if($this->ajax===null){
			$this->ajax=($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest');
		}
		return $this->ajax;
	}

	/*
		Verify if the request is an AMF

		Return
			boolean
	*/
	public function isFlash(){
		if($this->flash===null){
			$this->flash=($_SERVER['CONTENT_TYPE']=='application/x-amf');
		}
		return $this->flash;
	}

	/*
		Verify if the request is secure (with HTTPS)

		Return
			boolean
	*/
	public function isSecure(){
		if($this->secure===null){
			$this->secure=$_SERVER['HTTPS']=='on';
		}
		return $this->secure;
	}

	/*
		Return the client IP

		Return
			string
	*/
	public function getClientIp(){
		if(!$this->ip){
			// Proxy test
			if($_SERVER['HTTP_CLIENT_IP']){
				$this->ip=$_SERVER['HTTP_CLIENT_IP'];
			}
			elseif($_SERVER['HTTP_X_FORWARDED_FOR']){
				$this->ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			// Direct retrieving
			else{
				$this->ip=$_SERVER['REMOTE_ADDR'];
			}
		}
		return $this->ip;
	}

}
