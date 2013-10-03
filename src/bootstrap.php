<?php

require_once __DIR__.'/../vendor/autoload.php';

require __DIR__.'/config.php';

require __DIR__.'/model/users.php';

$app = new Silex\Application();

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

$app->register(new Silex\Provider\SessionServiceProvider()); 
$app->register(new Silex\Provider\ServiceControllerServiceProvider()); 
$app->register(new Silex\Provider\UrlGeneratorServiceProvider()); 
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());


// register database 

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbhost' => 'localhost',
        'dbname' => 'speleo2_aaol',
        'user' => 'speleo2_aaol',
        'password' => SQL_PASSWORD,
    ),
));

// register providers
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/../views',
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(

		'security.firewalls' => array(
		    'admin' => array(
		        'anonymous'=> true,
		        'pattern' => '^/',
		        'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
				'logout' => array('logout_path' => '/logout'), // url to call for logging out
		        'users' => $app->share(function () use ($app) {
				    return new UserProvider($app['db']);
				}),
		    ),
		),
		'security.access_rules' => array(
		    array('^/admin','ROLE_ADMIN'),
		),
		'security.access_rules' => array(
		    array('^/settings','ROLE_USER'),
		)

	)
	
);

$app['security.role_hierarchy'] = array(
    'ROLE_ADMIN' => array('ROLE_USER'),
);

$app['security.encoder.digest'] = $app->share(function ($app) {
    // use the sha1 algorithm
    // don't base64 encode the password
    // use only 1 iteration
    return new MessageDigestPasswordEncoder('sha1', false, 1);
});

$app['debug'] = true;

return $app;