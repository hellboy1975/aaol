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

$app->match('/admin/newpost', function (Request $request) use ($app) {
    // some default data for when the form is displayed the first time
    $data = array(
        'title' => 'Post title'
    );

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('title')
        ->add('content', 'textarea', array(
			'attr' => array('class' => 'html-editor'),
        ))
        ->add('type', 'choice', array(
            'choices' => array(POST_TYPE_NEWS => 'News', POST_TYPE_USER => 'User'),
            'expanded' => false,
            'multiple' => false,
        ))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {
            $data = $form->getData();

            // do something with the data

            // redirect somewhere
            return $app->redirect('/');
        }
    }

    // display the form
    return $app['twig']->render('newpost.twig', array('form' => $form->createView()));
});


return $app;