<?php
date_default_timezone_set('Asia/Kolkata');
use Phalcon\DI\FactoryDefault,
	Phalcon\Mvc\Micro,
	Phalcon\Http\Response,
	Phalcon\Http\Request;

 

$loader = new \Phalcon\Loader();

$loader->registerDirs(array(
    __DIR__ . '/models/',__DIR__ . '/controllers/'
))->register();

//$loader->registerDirs(array(
//    __DIR__ . '/controllers/'
//))->register();


$di = new FactoryDefault();

$di->set('mongo', function() {
    $mongo = new MongoClient();
    return $mongo->selectDB("Sociabile");
}, true);

$di->set('collectionManager', function(){
    return new Phalcon\Mvc\Collection\Manager();
}, true);

$di->set('dispatcher', function(){

    //Create an event manager
    $eventsManager = new EventsManager();

    //Attach a listener for type "dispatch"
    $eventsManager->attach("dispatch", function($event, $dispatcher) {
        //...
    });

    $dispatcher = new MvcDispatcher();

    //Bind the eventsManager to the view component
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;

}, true);
//Using an anonymous function, the instance will be lazy loaded
$di["response"] = function () {
	return new Response();
};
$di["request"] = function () {
	return new Request();
};
$app = new Micro();
$app->setDI( $di );
