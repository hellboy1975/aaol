<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();



$app->register(new Silex\Provider\SessionServiceProvider()); 
$app->register(new Silex\Provider\ServiceControllerServiceProvider()); 
$app->register(new Silex\Provider\UrlGeneratorServiceProvider()); 

// register providers
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/../views',
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
	    'admin' => array(
	        'pattern' => '^/admin',
	        'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
	        'logout' => array('logout_path' => '/logout'), // url to call for logging out
	        'users' => array(
	            'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
	        ),
	    ),
	    'default' => array(
            'pattern' => '^.*$',
            'anonymous' => true, // Needed as the login path is under the secured area
            'form' => array('login_path' => '/', 'check_path' => 'login_check'),
            'logout' => array('logout_path' => '/logout'), // url to call for logging out
        ),
	),
));



$app['debug'] = true;




return $app;