<?php

// Posts controller




use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


 

$postController = $app['controllers_factory'];




/**
 *  post/edit controller
 */
$postController->match('/edit/{id}', function (Silex\Application $app, Request $request, $id)  
{
    $p = new PostsModel( $app['db'], $app );
    $post = $p->fetchPost($id);

    $user = $app['security']->getToken()->getUser();

    // first thing we should check if the user has permission to edit this post!


    if  (  ($user->getID() != $post['user_id']) && !( in_array('ROLE_ADMIN', $user->getRoles() )) ) {
        $app['twig']->render('denied.twig');
    }

    // The categories a user may choose from is limited by their role    
    if ( in_array('ROLE_ADMIN', $user->getRoles() )) $categoryChoices = array(POST_TYPE_NEWS => 'News', POST_TYPE_USER => 'User', POST_TYPE_DEV => 'Development');
    else $categoryChoices = array(POST_TYPE_USER => 'User');

    // generate the form handle
    $form = $p->createForm($post, $categoryChoices);

    // iof it's a GET it means we're showing the Edit Post page
    if ('GET' == $request->getMethod()) 
    {

            // display the form
            return $app['twig']->render('newpost.twig', array( 
                'form' => $form->createView(), 
                'post_submit_label' => 'Update' 
                ) 
            );
    }

    // if it's a POST it means we are submitting a change
    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) 
        {
            $data = $form->getData();
            
            $p->updatePost($id, $data);
            
            // redirect to the post page
            return $app->redirect('/post/view/' . $id);

        }
    }
    
    return $app->abort(404, "Not sure what the hell you are doing!");
});

/**
 * post/new controller
 */
$postController->match('/new', function (Request $request) use ($app) {

	$user = $app['security']->getToken()->getUser();

    // some default data for when the form is displayed the first time
    $data = array(
        'title' => 'Post title',
        'slug' => 'post-title'
    );

	// The categories a user may choose from is limited by their role    
    if ( in_array('ROLE_ADMIN', $user->getRoles() )) $categoryChoices = array(POST_TYPE_NEWS => 'News', POST_TYPE_USER => 'User', POST_TYPE_DEV => 'Development');
    else $categoryChoices = array(POST_TYPE_USER => 'User');


    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('title')
        ->add('slug')
        ->add('content', 'textarea', array(
			'attr' => array('class' => 'html-editor'),
        ))
        ->add('category', 'choice', array(
            'choices' => $categoryChoices,
            'expanded' => false,
            'multiple' => false,
        ))
        ->add('allow_comments', 'choice', array(
            'choices' => array(1 => 'Yes', 0 => 'No'),
            'expanded' => false,
        ))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) 
        {
            $data = $form->getData();
            
			$data['user_id'] = $user->getID();

            $p = new PostsModel( $app['db'], $app );
            $lastID = $p->insertPost($data);

            // redirect to the post page
            return $app->redirect('/post/view/' . $lastID);

        }
    }
    // display the form
    return $app['twig']->render('newpost.twig', array('form' => $form->createView()));
});

$postController->get('/view/{id}', function (Silex\Application $app, $id)  {

	$p = new PostsModel( $app['db'], $app );
	$post = $p->fetchPost($id);

    return $app['twig']->render('single-post.twig', array(
    	'post' => $post,
    ));
	// check to see if user can edit this post
    return $app->abort(404, "Post $id does not exist.");
})->bind('viewpost');