<?php

// Load classes

use Symfony\Component\Finder\Finder;

require '../vendor/autoload.php';
require '../../vendor/autoload.php';

$finder = new Finder();
$finder->files()->in('../../src');
foreach($finder as $file) {
	require_once $file->getRealpath();
}

$lumy = new Lumy\Http();

$lumy->get('/get', function() use($lumy) {
	header('Content-Type: application/json');
	echo json_encode(array('status' => 'ok'));
});

$lumy->post('/post', function() use($lumy) {
	header('Content-Type: application/json');
	echo json_encode(array('status' => 'ok'));
});

$lumy->put('/put', function() use($lumy) {
	header('Content-Type: application/json');
	echo json_encode(array('status' => 'ok'));
});

$lumy->delete('/delete', function() use($lumy) {
	header('Content-Type: application/json');
	echo json_encode(array('status' => 'ok'));
});

$lumy->map('PATCH', '/patch', function() use($lumy) {
	header('Content-Type: application/json');
	echo json_encode(array('status' => 'ok'));
});

$lumy->get('/redirect', function() use($lumy) {
	$lumy['response']->redirect('/');
});

$lumy->get('/article/{id}/comments', function() {}, null, null, 'test');

$lumy->get('/assembleurl', function() use($lumy) {
	header('Content-Type: application/json');
	echo json_encode(array(
		'status' => $lumy->assembleUrl('test', array('id' => 72)) == '/article/72/comments' ? 'ok' : 'failed'
	));
});

$lumy->get(array('/', '/foo/bar'), function() use($lumy) {
	header('Content-Type: application/json');
	
	$lumy['response']->setHeader('TEST_RESPONSE', 'ok');
	$lumy['response']->setHeader('TEST_RESPONSE2', 'failed');
	$lumy['response']->unsetHeader('TEST_RESPONSE2');
	
	$status = $lumy['response']->getStatus();
	$lumy['response']->setStatus(201);
	
	echo json_encode(array(
		'chain' => $lumy['request']->getChain(),
		'scheme' => $lumy['request']->getScheme(),
		'host' => $lumy['request']->getHost(),
		'port' => $lumy['request']->getPort(),
		'requesturi' => $lumy['request']->getRequestUri(),
		'rooturi' => $lumy['request']->getRootUri(),
		'resourceuri' => $lumy['request']->getResourceUri(),
		'method' => $lumy['request']->getMethod(),
		'TEST_RESPONSE' => $lumy['request']->getHeader('TEST_RESPONSE'),
		'TEST_RESPONSE2' => $lumy['response']->getHeader('TEST_RESPONSE'),
		'TEST_RESPONSE3' => $lumy['response']->getHeader('TEST_RESPONSE2'),
		'status' => $status,
	));
});

$lumy->publish('robots.txt');
$lumy->publish('/css');
$lumy->unpublish('/css/styles2.css');

$lumy->run();