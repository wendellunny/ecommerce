<?php 

require_once("vendor/autoload.php");
use \Slim\Slim;
use \Hcode\Page;
$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
	$page = new Page();
	$page -> setTpl("index");
	
});

$app->get('/opa',function(){
	require_once('pages/opa.html');
});

$app->run();

 ?>