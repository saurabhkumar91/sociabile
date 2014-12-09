<?php
require_once('library.php');

$app->post( '/registration', function () use ( $app ) {
    $user = new UsersController();
    $user->registrationAction($app->request->getPost());
});

$app->post( '/generateToken', function () use ( $app ) {
    $header_data = getallheaders();
    $user = new UsersController();
    $user->generateTokenAction($header_data,$app->request->getPost());
});

$app->post( '/codeVerification', function () use ( $app ) {
    $header_data = getallheaders();
    $user = new UsersController();
    $user->codeVerificationAction($header_data,$app->request->getPost());
}); 

$app->post( '/sendContacts', function () use ( $app ) {
    $header_data = getallheaders();
    $user = new UsersController();
    $user->sendContactsAction($header_data,$app->request->getPost());
});

$app->get( '/setDisplayName/{name}', function ($name) use ( $app ) {
    $header_data = getallheaders();
    $user = new UsersController();
    $user->setDisplayNameAction($header_data,$name);
});

$app->get( '/getProfile', function () use ( $app ) {
    $header_data = getallheaders();
    $user = new UsersController();
    $user->getProfileAction($header_data);
});

$app->get( '/getIndicators', function () use ( $app ) {
    $user = new UsersController();
    $user->getIndicatorsAction();
});

$app->post( '/setProfile', function () use ( $app ) {
    $header_data = getallheaders();
    $user = new UsersController();
    $user->setProfileAction($header_data,$app->request->getPost());
});

$app->notFound(
	function () use ( $app ) {
            $app->response->setStatusCode( 404, "Not Found" )->sendHeaders();
            echo 'This is crazy, but this page was not found!';
	}
);
$app->handle();
