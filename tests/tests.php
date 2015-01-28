<?php

use Symfony\Component\Finder\Finder;

########################################################### PHP

ini_set('display_errors',true);
error_reporting(E_ALL);

########################################################### Load classes

require 'vendor/autoload.php';
require '../vendor/autoload.php';
require '../src/Lumy.php';

$finder=new Finder();
$finder->files()->in('../src');
foreach($finder as $file){
	require_once $file->getRealpath();
}

########################################################### Instantiate

$name='Lumy';

$minisuite=new MiniSuite($name);

########################################################### CLI request object

if(PHP_SAPI=='cli'){
	$minisuite->group('Request object',function() use($minisuite){

		$_SERVER['argv']=array('myapp','install','gallery');
		$request=new Lumy\Request\Cli;

		$minisuite->expects('getChain()')
				  ->that($request->getChain())
				  ->equals('install gallery');

		$minisuite->expects('getApplicationName()')
				  ->that($request->getApplicationName())
				  ->equals('myapp');

		$minisuite->expects('getArguments()')
				  ->that($request->getArguments())
				  ->equals(array('install','gallery'));

	});
}

########################################################### HTTP request object

if(PHP_SAPI!='cli'){
	$minisuite->group('Request object',function() use($minisuite){

		/*
			TODO : write overall real environment tests
		*/

	});
}

########################################################### CLI response object

if(PHP_SAPI=='cli'){
	$minisuite->group('Response object',function() use($minisuite){

		$response=new Lumy\Response\Cli;
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
		$contents=ob_end_clean();

		$minisuite->expects('__toString()')
				  ->that($contents)
				  ->equals('couettes pwet chouette');

		$response->setVariable('pwet',72);

		$minisuite->expects('Set/get environment variable')
				  ->that($response->getVariable('pwet'))
				  ->equals(72);

		$response->setVariable('pwet',null);

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

if(PHP_SAPI!='cli'){
	$minisuite->group('Response object',function() use($minisuite){

		/*
			TODO : write overall real environment tests
		*/

	});
}

########################################################### CLI application

if(PHP_SAPI=='cli'){
	$minisuite->group('Application',function() use($minisuite){

		$_SERVER['argv']=array('myapp','install','gallery','7');

		$lumy=new Lumy\Cli;
		$lumy->route('install gallery {id}',function($id) use($lumy){
			$lumy['response']->appendBody('controller '.$id);
		},null,null,'gallery');
		$lumy->middleware(function($middlewares) use($lumy){
			$lumy['response']->appendBody('middleware1 ');
			$middlewares->next();
		});
		$lumy->middleware(function($middlewares) use($lumy){
			$lumy['response']->appendBody('middleware2 ');
			$middlewares->next();
		});

		$minisuite->expects('Basics')
				  ->that($lumy->run()->getBody())
				  ->equals('middleware1 middleware2 controller 7');

		$minisuite->expects('Named route')
				  ->that($lumy['router']['gallery'])
				  ->isInstanceOf('Lumy\Router\Route');

		$lumy=new Lumy\Cli;
		$lumy->error(function($e) use($minisuite,$lumy){

			$minisuite->expects('Error')
					  ->that($e)
					  ->isInstanceOf('Exception');

		});
		$lumy->run();

	});
}

########################################################### HTTP application

if(PHP_SAPI!='cli'){
	$minisuite->group('Application',function() use($minisuite){

		/*
			TODO : write overall real environment tests
		*/

	});
}