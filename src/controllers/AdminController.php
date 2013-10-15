<?php

// AdminController.php
 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$admin = $app['controllers_factory'];


$admin->get('/', function () use ($app) {
    return $app['twig']->render('admin.twig');
});


$admin->match('/user/{username}', function (Request $request, $username) use ($app) {

	$u = new UserProvider( $app['db'] );

	$data = $u->fetchUser($username);

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('first_name', 'text', array(
        	'label' => 'First Name',
            'attr' => array('class' => 'form-control')  
        ))
        ->add('last_name', 'text', array(
        	'label' => 'Last Name',
            'attr' => array('class' => 'form-control')
        ))
        ->add('email', 'email', array(
            'attr' => array('class' => 'form-control'),
        ))
        ->add('bio', 'textarea', array(
            'attr' => array('class' => 'html-editor'),
        ))
        ->add('status', 'choice', array(
            'choices' => array(USER_STATUS_ACTIVE => 'Active', USER_STATUS_INACTIVE => 'Inactive', USER_STATUS_REG_PENDING => 'Registration Pending'),
            'expanded' => false,
            'multiple' => false,
            'attr'=> array('class'=>'form-control')
        ))
        ->add('roles', 'choice', array(
            'choices' => array(USER_ROLE_ADMIN => 'Administrator', USER_ROLE_USER => 'User'),
            'expanded' => false,
            'multiple' => false,
            'attr'=> array('class'=>'form-control')
        ))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) 
        {
            $data = $form->getData();
            
			$app['db']->update('user', $data, array('id' => $user->getID()));

            // redirect back to the homepage for now
            return $app->redirect('/settings');

        }
    }
    // display the form
    return $app['twig']->render('user-settings.twig', array('form' => $form->createView(), 'user_data' => $data));
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

	// fetch all the posts
	$status = $app['request']->query->get('status');
	$roles = $app['request']->query->get('roles');
	$offset = $app['request']->query->get('offset');

	if ($status == '') $status = POST_TYPE_ALL;
	if ($roles == '') $roles = POST_TYPE_ALL;

	$u = new UserProvider( $app['db'] );

	$users = $u->fetchUsers($roles, $status);

    return $app['twig']->render('admin/users.twig', array(
    	'users' => $users,
    	'filter_roles' => $roles,
    	'filter_status' => $status,
    	'lastSQL' => $u->lastSQL
    	)
    );
});





// TODO: settings

