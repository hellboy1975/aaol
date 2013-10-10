<?php

// UserController

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints as Assert;

$user = $app['controllers_factory'];

$user->match('/', function (Request $request) use ($app) {

	$user = $app['security']->getToken()->getUser();

	$userFields = $user->getFields();

    // some default data for when the form is displayed the first time
    $data = array(
        'first_name' => $userFields['first_name'],
        'last_name' => $userFields['last_name'],
        'email' => $userFields['email']
    );



    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('first_name', 'text', array(
        	'label' => 'First Name'
        ))
        ->add('last_name', 'text', array(
        	'label' => 'Last Name'
        ))
        ->add('email', 'email')
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
    return $app['twig']->render('default_form.twig', array('form' => $form->createView()));
});



/**
 * Change password controller
 */
$user->match('/password', function (Request $request) use ($app) {

	$user = $app['security']->getToken()->getUser();

    $form = $app['form.factory']->createBuilder('form', $data)
  //       ->add('new-password', 'password', array(
		//     'label'  => 'New Password',
		// ))
  //       ->add('confirm-password', 'password', array(
		//     'label'  => 'Confirm Password',
		// ))
		->add('password', 'repeated', array(
		    'type' => 'password',
		    'invalid_message' => 'The password fields must match.',
		    'options' => array('attr' => array('class' => 'password-field')),
		    'required' => true,
		    'first_options'  => array('label' => 'Password'),
		    'second_options' => array('label' => 'Repeat Password'),
		))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) 
        {
            $data = $form->getData();

            // foo sha1 0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33

            $raw = $data['password'];
			
			$password = $app['security.encoder.digest']->encodePassword($raw, $user->getSalt());

        	$app['db']->update('user', array('password' => $password), array('id' => $user->getID()));

            // back to the settings page - show an error?
            return $app->redirect('/settings/password');

        }
    }
    // display the form
    return $app['twig']->render('default_form.twig', array('form' => $form->createView()));
});