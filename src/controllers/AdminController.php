<?php

// AdminController.php
 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


 

$admin = $app['controllers_factory'];


$admin->get('/', function () use ($app) {
    return $app['twig']->render('admin.twig');
});


// TODO: posts

// TODO: settings

