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

/**
 *  Top level Types route
 */

$admin->match('/types', function (Request $request) use ($app) {

	$c = new CodesModel( $app['db'], $app );

	$codes = $c->fetchTypes();
	$typeCodes = $c->fetchTypeCodes();

	$form = $app['form.factory']->createBuilder('form', $data)
        ->add('code', 'text', array(
        	'label' => 'Code',
            'attr' => array('class' => 'form-control')  
        ))
        ->add('description', 'text', array(
        	'label' => 'Description',
            'attr' => array('class' => 'form-control')
        ))
        ->add('parent_type', 'choice', array(
            'choices' => $typeCodes,
            'label' => 'Parent Type',
            'expanded' => false,
            'multiple' => false,
            'attr'=> array('class'=>'form-control')
        ))
        ->add('force_parent', 'text', array(
            'label' => 'Force Parent',
            'attr' => array('class' => 'form-control')
        ))
        ->getForm();

	if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) 
        {
            $data = $form->getData();

            $data['type'] = "TYPE";
            
			$app['db']->insert('code', $data);

            // redirect back to the homepage for now
            return $app->redirect('/admin/types');

        }
    }        

    return $app['twig']->render('admin/codes.twig', array(
    	'codes' => $codes,
    	'form' => $form->createView()
    	)
    );
});

/**
 * Fetches all the codes that belong to a certain type
 */
$admin->match('/types/{type}', function (Request $request, $type) use ($app) {

	$c = new CodesModel( $app['db'], $app );

	
    $parent = $c->getSingleCode($type);
    
    $typeCodes = $c->fetchTypeCodes($parent['type']);

	$form = $app['form.factory']->createBuilder('form', $data)
        ->add('code', 'text', array(
        	'label' => 'Code',
            'attr' => array('class' => 'form-control')  
        ))
        ->add('description', 'text', array(
        	'label' => 'Description',
            'attr' => array('class' => 'form-control')
        ))->getForm();

    if ( $parent['force_parent'] != '') 
    {
        $form->add('parent', 'hidden', array(
            'data' => $parent['force_parent'],
        ));
    }
    else 
    {
        $form->add('parent', 'choice', array(
            'choices' => $typeCodes,
            'label' => 'Parent Type',
            'expanded' => false,
            'attr'=> array('class'=>'form-control')
        ));
    }


	if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) 
        {
            $data = $form->getData();

            $data['type'] = $parent['code'];
            $data['parent_type'] = $parent['parent_type'];
            
			$app['db']->insert('code', $data);

            // redirect back to the homepage for now
            return $app->redirect("/admin/types/$type");

        }
    }   

    // fetch the list of codes for this type
    $codes = $c->fetchCodes($type);

    return $app['twig']->render('admin/codes.twig', array(
    	'codes' => $codes,
    	'type' => $type,
    	'form' => $form->createView(),
        'force_parent' => $parent['force_parent']
    	)
    );
});

/**
 * Fetches all the codes that belong to a certain type
 */
$admin->match('/types/edit/{type}/{code}', function (Request $request, $type, $code) use ($app) {

    $c = new CodesModel( $app['db'], $app );

    
    $data = $c->fetchSingleCode($type, $code);
    

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('code', 'text', array(
            'label' => 'Code',
            'attr' => array('class' => 'form-control')  
        ))
        ->add('description', 'text', array(
            'label' => 'Description',
            'attr' => array('class' => 'form-control')
        ))
        ->add('type', 'text', array(
            'label' => 'Type',
            'attr' => array('class' => 'form-control')
        ))
        ->add('parent_type', 'text', array(
            'label' => 'Parent Type',
            'required' => false,
            'attr' => array('class' => 'form-control')
        ))
        ->add('parent', 'text', array(
            'label' => 'Parent Code',
            'required' => false,
            'attr' => array('class' => 'form-control')
        ))
        ->add('force_parent', 'text', array(
            'label' => 'Force Parent',
            'required' => false,
            'attr' => array('class' => 'form-control')
        ))
        ->getForm();


    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) 
        {
            $data = $form->getData();

            
            $c->updateCode($data);

            return $app->redirect("/types/edit/$type");

        }
    }   


    return $app['twig']->render('admin/edit-code.twig', array(
        'form' => $form->createView(),
        'breadcrumb' => "$type - $code"
        )
    );
});


$admin->get('/types/{type}/{parentCode}', function ($type, $parentCode) use ($app) {

	$c = new CodesModel( $app['db'], $app );

	$codes = $c->fetchCodesByTypeParent($type, $parentCode);

    return $app['twig']->render('admin/codes.twig', array(
    	'codes' => $codes,
    	'type' => $type,
    	'parent_code' => $parentCode
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

