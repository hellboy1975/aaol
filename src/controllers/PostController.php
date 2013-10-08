<?php

// Posts controller

// Note that the new post functionality is actually under user as it's held behind a security firewall


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


 

$posts = $app['controllers_factory'];


// $posts->get('/', function () use ($app) {
//     return $app['twig']->render('admin.twig');
// });

$posts->get('/{id}', function (Silex\Application $app, $id)  {

	$p = new PostsModel( $app['db'], $app );
	$post = $p->fetchPost($id);

	//return print_r($post[0]);

    return $app['twig']->render('single-post.twig', array(
    	'post' => $post[0],
    ));
	// check to see if user can edit this post
    return $app->abort(404, "Post $id does not exist.");
});