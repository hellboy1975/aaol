<?php

/**
 * BOOTSTRAP
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

//$blog = require __DIR__.'/blog.php';

$app = require __DIR__.'/bootstrap.php';

require __DIR__.'/controllers/AdminController.php';
require __DIR__.'/controllers/UserController.php';


/**
 * APP DEFINITION
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

use Symfony\Component\HttpFoundation\Request;


$app->get('/', function () use ($app) {

	$p = new PostsModel( $app['db'], $app );
	$posts = $p->getPosts();

    return $app['twig']->render('index.twig', array(
    	'posts' => $posts,
    ));
})
->bind('home');

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})
->bind('login');

$app->mount('/admin', $admin);
$app->mount('/settings', $user);


return $app;