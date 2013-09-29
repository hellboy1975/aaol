<?php

/**
 * BOOTSTRAP
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

//$blog = require __DIR__.'/blog.php';

$app = require __DIR__.'/bootstrap.php';

//$app->mount('/blog', $blog);


/**
 * APP DEFINITION
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

use Symfony\Component\HttpFoundation\Request;


$app->get('/', function () use ($app) {

	$p = new PostsModel( $app['db'] );
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
});

$app->get('/admin', function () use ($app) {
    return $app['twig']->render('admin.twig');
});


return $app;