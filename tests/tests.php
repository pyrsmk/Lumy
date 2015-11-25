<?php

/*
	There are some features that are not tested because it depends of the HTTP environment,
	the code is simple, and no bug has been found.
	
	Here's the list of the untested features :
	
	HTTP request object
	-------------------
	
	- isAjax()
	- isFlash()
	- isSecure()
	- getClientIp()
	
	HTTP response object
	--------------------
	
	- send()
	- display()
*/

use Symfony\Component\Finder\Finder;

########################################################### PHP

ini_set('display_errors', true);
error_reporting(E_ALL);

########################################################### Load classes

require 'vendor/autoload.php';
require '../vendor/autoload.php';

$finder = new Finder();
$finder->files()->in('../src');
foreach($finder as $file) {
	require_once $file->getRealpath();
}

$minisuite = new MiniSuite('Lumy');

if(PHP_SAPI != 'cli') {
	echo '<pre>';
}

########################################################### Resquests/HTTP parameters

$c = new Chernozem();

$c['http_scheme'] = 'http';
$c['http_host'] = 'localhost';
$c['http_port'] = 80;
$c['http_rooturi'] = '/PHP/Lumy/tests/http';
$c['http_chain'] = $c['http_scheme'].'://'.$c['http_host'].$c['http_rooturi'];

########################################################### CLI request object

if(PHP_SAPI == 'cli') {
	$minisuite->group('Request object', function() use($minisuite) {
		$_SERVER['argv'] = array('myapp', 'install', 'gallery');
		$request = new Lumy\Request\Cli();

		$minisuite->expects('getChain()')
				  ->that($request->getChain())
				  ->equals('install gallery');

		$minisuite->expects('getApplicationName()')
				  ->that($request->getApplicationName())
				  ->equals('myapp');

		$minisuite->expects('getArguments()')
				  ->that($request->getArguments())
				  ->equals(array('install', 'gallery'));
	});
}

########################################################### HTTP request object

if(PHP_SAPI != 'cli') {
	$minisuite->group('Request object', function() use($minisuite, $c) {
		
		foreach(array('/', '/foo/bar') as $resourceuri) {
			
			$minisuite->group($resourceuri, function() use($minisuite, $c, $resourceuri) {
				$request = Requests::get(
					$c['http_chain'].$resourceuri,
					array('TEST_RESPONSE' => 'ok')
				);
				$data = json_decode($request->body);
				
				//debug($request->body);
				
				$minisuite->expects('getChain()')
						  ->that($data->chain)
						  ->equals($c['http_chain'].$resourceuri);
				
				$minisuite->expects('getScheme()')
						  ->that($data->scheme)
						  ->equals($c['http_scheme']);
				
				$minisuite->expects('getHost()')
						  ->that($data->host)
						  ->equals($c['http_host']);
				
				$minisuite->expects('Port()')
						  ->that($data->port)
						  ->equals($c['http_port']);
				
				$minisuite->expects('getRequestUri()')
						  ->that($data->requesturi)
						  ->equals($c['http_rooturi'].$resourceuri);
				
				$minisuite->expects('getRootUri()')
						  ->that($data->rooturi)
						  ->equals($c['http_rooturi']);
				
				$minisuite->expects('getResourceUri()')
						  ->that($data->resourceuri)
						  ->equals($resourceuri);
				
				$minisuite->expects('getMethod()')
						  ->that($data->method)
						  ->equals('GET');
				
				$minisuite->expects('getHeader()')
						  ->that($data->TEST_RESPONSE)
						  ->equals('ok');
			});
			
		}
		
	});
}

########################################################### CLI response object

if(PHP_SAPI == 'cli') {
	$minisuite->group('Response object', function() use($minisuite) {

		$response = new Lumy\Response\Cli;
		$response->setBody('pwet');

		$minisuite->expects('setBody()')
				  ->that($response->getBody())
				  ->equals('pwet');

		$response->prependBody('couettes ');

		$minisuite->expects('setBody()')
				  ->that($response->getBody())
				  ->equals('couettes pwet');

		$response->appendBody(' chouette');

		$minisuite->expects('setBody()')
				  ->that($response->getBody())
				  ->equals('couettes pwet chouette');

		ob_start();
		echo $response;
		$contents = ob_end_clean();

		$minisuite->expects('__toString()')
				  ->that($contents)
				  ->equals('couettes pwet chouette');

		$response->setVariable('pwet', 72);

		$minisuite->expects('Set/get environment variable')
				  ->that($response->getVariable('pwet'))
				  ->equals(72);

		$response->setVariable('pwet', null);

		$minisuite->expects('Set/get environment variable to NULL')
				  ->that($response->getVariable('pwet'))
				  ->equals(null);

		$response->unsetVariable('pwet');

		$minisuite->expects('Unset environment variable')
				  ->that($response->getVariable('pwet'))
				  ->equals(false);

		$minisuite->expects('colorize() [simple]')
				  ->that($response->colorize(Lumy\Response\Cli::PURPLE))
				  ->equals("\033[0;35m");

		$minisuite->expects('colorize() [advanced]')
				  ->that($response->colorize(Lumy\Response\Cli::GREEN,Lumy\Response\Cli::RED,Lumy\Response\Cli::BLINK))
				  ->equals("\033[41m\033[5;32m");

		$minisuite->expects('reset()')
				  ->that($response->reset())
				  ->equals("\033[0m");

	});
}

########################################################### HTTP response object

if(PHP_SAPI != 'cli') {
	$minisuite->group('Response object', function() use($minisuite, $c) {
		
		$request = Requests::get(
			$c['http_chain'],
			array('TEST_RESPONSE' => 'ok')
		);
		$data = json_decode($request->body);

		$minisuite->expects('Headers : test 1')
				  ->that($request->headers['test_response'])
				  ->equals('ok');

		$minisuite->expects('Headers : test 2')
				  ->that($data->TEST_RESPONSE2)
				  ->equals('ok');

		$minisuite->expects('Headers : test 3')
				  ->that($data->TEST_RESPONSE3)
				  ->isEmpty();

		$minisuite->expects('getStatus()')
				  ->that($data->status)
				  ->equals(200);

		$minisuite->expects('setStatus()')
				  ->that($request->status_code)
				  ->equals(201);
		
		$request = Requests::get(
			$c['http_chain'].'/redirect',
			array(),
			array('follow_redirects' => false)
		);		
		$minisuite->expects('redirect()')
				  ->that($request->status_code)
				  ->equals(302);
		

	});
}

########################################################### CLI application

if(PHP_SAPI == 'cli') {
	$minisuite->group('Application', function() use($minisuite) {

		$_SERVER['argv'] = array('myapp','install','gallery','7');

		$lumy = new Lumy\Cli;
		
		$lumy->route('install gallery {id}', function($id) use($lumy) {
			$lumy['response']->appendBody('controller'.$id);
		}, null, null, 'gallery');
		
		$lumy->middleware(function($middlewares) use($lumy) {
			$lumy['response']->appendBody('middleware1 ');
			$middlewares->next();
		});
		
		$lumy->middleware(function($middlewares) use($lumy) {
			$lumy['response']->appendBody('middleware2 ');
			$middlewares->next();
		});

		$minisuite->expects('Basics')
				  ->that($lumy->run()->getBody())
				  ->equals('middleware1 middleware2 controller7');

		$minisuite->expects('Named route')
				  ->that($lumy['router']['routes']['gallery'])
				  ->isInstanceOf('Lumy\Router\Route');

		$lumy=new Lumy\Cli;
		$lumy->error(function($e) use($minisuite, $lumy) {
			$minisuite->expects('Error controller')
					  ->that($e)
					  ->isInstanceOf('Exception');
		});
		$lumy->run();

	});
}

########################################################### HTTP application

if(PHP_SAPI != 'cli') {
	$minisuite->group('Application', function() use($minisuite, $c) {
		
		$request = Requests::get($c['http_chain'].'/robots.txt');
		$minisuite->expects('publish()/unpublish() : test 1')
				  ->that($request->status_code)
				  ->equals(200);
		
		$request = Requests::get($c['http_chain'].'/css/styles.css');
		$minisuite->expects('publish()/unpublish() : test 2')
				  ->that($request->status_code)
				  ->equals(200);
		
		$request = Requests::get($c['http_chain'].'/css/styles2.css');
		$minisuite->expects('publish()/unpublish() : test 3')
				  ->that(strpos($request->body, 'exception'))
				  ->doesNotEqual(false);
		
		$request = Requests::get($c['http_chain'].'/get');
		$data = json_decode($request->body);
		$minisuite->expects('GET method')
				  ->that($data->status)
				  ->equals('ok');
		
		$request = Requests::post($c['http_chain'].'/post');
		$data = json_decode($request->body);
		$minisuite->expects('POST method')
				  ->that($data->status)
				  ->equals('ok');
		
		$request = Requests::put($c['http_chain'].'/put');
		$data = json_decode($request->body);
		$minisuite->expects('PUT method')
				  ->that($data->status)
				  ->equals('ok');
		
		$request = Requests::delete($c['http_chain'].'/delete');
		$data = json_decode($request->body);
		$minisuite->expects('DELETE method')
				  ->that($data->status)
				  ->equals('ok');
		
		$request = Requests::patch($c['http_chain'].'/patch', array());
		$data = json_decode($request->body);
		$minisuite->expects('PATCH method')
				  ->that($data->status)
				  ->equals('ok');
		
		$request = Requests::get($c['http_chain'].'/assembleurl');
		$data = json_decode($request->body);
		$minisuite->expects('assembleUrl()')
				  ->that($data->status)
				  ->equals('ok');

	});
}