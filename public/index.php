<?php
session_start();

use lib\Confg;


require '../vendor/autoload.php';
require '../common.php';
require '../config.php';




$app = new \Slim\App(array(
    'debug' => true,
    'displayErrorDetails' => true,
));


// Fetch DI Container
$container = $app->getContainer();

// Register Twig View helper
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../templates', [
        'cache' => false,
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    return $view;
};

$container['session'] = function(){
    return new \SlimSession\Helper;
};


// Add middleware to the application
$app = new \Slim\App($container);

$app->add(new \Slim\Middleware\Session([
    'name' => 'StreetSweeper',
    'autorefresh' => true,
    'lifetime' => '24 hour'
]));


$checkLogin = function($request, $response, $next) use ($container){
    if( ! isset( $container->session->user['id'] ) )
    {
        return $response->withRedirect('/admin/login');
    }
    $container['view']['user'] = $container->session->user;
    $response = $next($request, $response);
    return $response;
};



// Automatically load router files
$routers = glob('../routers/*.router.php');
foreach ($routers as $router) {
    require $router;
}

$app->run();
