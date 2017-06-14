<?php

require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Saxulum\DoctrineMongoDb\Provider\DoctrineMongoDbProvider;

use Symfony\Component\HttpFoundation\JsonResponse;

$app = new Application();

// enable the debug mode
$app['debug'] = true;
$app->register(new MonologServiceProvider(), array(
  'monolog.logfile' => __DIR__.'/log/development.log',
));

$app->register(new DoctrineMongoDbProvider(), array(
  'mongodb.options' => array(
    'server' => 'mongodb://localhost:27017',
    'options' => array(
      'username' => 'root',
      'password' => 'root',
      'db' => 'admin'
    )
  )
));

$app->get('/', function(Application $app){
  $results = $app['mongodb']->selectDatabase('admin')->selectCollection('escuchas')->findOne();

  return new JsonResponse($results);
});

$app->run();
