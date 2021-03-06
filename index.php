<?php

require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Saxulum\DoctrineMongoDb\Provider\DoctrineMongoDbProvider;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
      'db' => 'admin'
    )
  )
));

$app->get('/', function(Application $app){
  $results = $app['mongodb']->selectDatabase('admin')->selectCollection('escuchas')->findOne();

  return new JsonResponse($results);
});

$app->get('/numbersForDate', function(Application $app, Request $request){
  $date = $request->query->get('date');
  $regex= "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
  if (preg_match($regex, $date)){
    $results = $app['mongodb']->selectDatabase('admin')->selectCollection('escuchas')->find(['fecha' => $date], ['numero' => true]);

    return new JsonResponse(iterator_to_array($results));
  } else {
    return new JsonResponse(['error'=> 'Date badly formatted. Try again with AAAA-MM-DD format.']);
  }
});

$app->get('/lastMessagesForNumber',function(Application $app, Request $request){
  $number = $request->query->get('number');
  $k = $request->query->get('k');
  if (preg_match("/^[0-9]{8}$/", $number) && preg_match("/^[0-9]*$/", $k)){
    $results = $app['mongodb']->selectDatabase('admin')->selectCollection('escuchas')->find(['numero'=> $number])->sort(['fecha'=> -1])->limit($k);
    return new JsonResponse(iterator_to_array($results));
  } else {
    return new JsonResponse(['error' => 'Parameters badly formatted. Number must be of 8 digits, and k an integer.']);
  }
});

$app->get('/wordInContent', function(Application $app, Request $request){
  $keyword = $request->query->get('keyword');
  $results = $app['mongodb']->selectDatabase('admin')->selectCollection('escuchas')->find(['$text' => ['$search' => $keyword]]);

  return new JsonResponse(iterator_to_array($results));
});

$app->run();
