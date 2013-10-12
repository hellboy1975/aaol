<?php

// AdminController.php
 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$admin = $app['controllers_factory'];


$admin->get('/', function () use ($app) {
    return $app['twig']->render('admin.twig');
});

$admin->get('/posts', function () use ($app) {

	// fetch all the posts
	$userID = $app['request']->query->get('user');
	$category = $app['request']->query->get('category');
	$offset = $app['request']->query->get('offset');

	if ($category == '') $category = POST_TYPE_ALL;

	$p = new PostsModel( $app['db'], $app );

	$p->filterOffset = $offset;
	$posts = $p->fetchPostsByCategoryUser($category, $userID);



    return $app['twig']->render('admin/posts.twig', array(
    	'posts' => $posts,
    	'filter_category' => $category,
    	'filter_user' => $userID,
    	'lastSQL' => $p->lastSQL
    	)
    );
});

$admin->get('/users', function () use ($app) {
    return $app['twig']->render('stub.twig');
});



// TODO: posts

// TODO: settings

