<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();


$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/../views',
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
));

$app['debug'] = true;


$app->get('/hello/{name}', function ($name) use ($app) {
    return $app['twig']->render('base.twig', array(
        'name' => $name,
    ));
});


return $app;