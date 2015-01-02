<?php
require_once('library.php');

$app->post('/registration', function () use ( $app ) {
    $user = new UsersController();
    $user->registrationAction($app->request->getPost());
});

$app->post('/generateToken', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->generateTokenAction($header_data,$app->request->getPost());
});

$app->post('/codeVerification', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->codeVerificationAction($header_data,$app->request->getPost());
}); 

$app->post('/sendContacts', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->sendContactsAction($header_data,$app->request->getPost());
});

$app->get('/setDisplayName/{name}', function ($name) use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->setDisplayNameAction($header_data,$name);
});

$app->get('/getProfile', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->getProfileAction($header_data);
});

$app->get('/getIndicators', function () use ( $app ) {
    $user = new UsersController();
    $user->getIndicatorsAction();
});

$app->post('/setProfile', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->setProfileAction($header_data,$app->request->getPost());
});

$app->put('/setContextIndicator/{context}', function ($context) use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->setContextIndicatorAction($header_data,$context);
});

$app->post('/createPost', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->createPostAction($header_data,$app->request->getPost());
});

$app->post('/postComment', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $comment = new CommentsController();
    $comment->postCommentsAction($header_data,$app->request->getPost());
});

$app->get('/getComments/{post_id}', function ($post_id) use ( $app ) {
    $header_data = Library::getallheaders();
    $comment = new CommentsController();
    $comment->getCommentsAction($header_data,$post_id);
});

$app->get('/getStatus/{user_id}', function ($user_id) use ( $app ) {
    $amazon = new AmazonsController();
    $amazon->getStatusAction($user_id);
});

$app->get('/createsignature', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $amazon = new AmazonsController();
    $amazon->createsignatureAction($header_data);
});

$app->get('/getRegisteredNumbers', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->getRegisteredNumbersAction($header_data);
});

$app->get('/getGroups', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->getGroupsAction($header_data);
});

$app->post('/sendRequest', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $friends = new FriendsController();
    $friends->sendRequestAction($header_data,$app->request->getPost());
});

$app->get('/pendingRequest', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $friends = new FriendsController();
    $friends->pendingRequestAction($header_data);
});

$app->post('/requestAccept', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $friends = new FriendsController();
    $friends->requestAcceptAction($header_data,$app->request->getPost());
});

$app->get('/getFriends', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $friends = new FriendsController();
    $friends->getFriendsAction($header_data);
});

$app->post('/generateOTP', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->generateOTPAction($header_data,$app->request->getPost());
});


$app->post('/changeNumber', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->changeNumberAction($header_data,$app->request->getPost());
});

$app->post('/addGroup', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->addGroupAction($header_data,$app->request->getPost());
});


$app->get('/aboutChat', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->aboutChatAction($header_data);
});


$app->get('/getCategory', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->getCategoryAction($header_data);
});

$app->post('/contactUs', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->contactUsAction($header_data,$app->request->getPost());
});

$app->post('/setPassword', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->setPasswordAction($header_data,$app->request->getPost());
});

$app->post('/resetPassword', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->resetPasswordAction($header_data,$app->request->getPost());
});

$app->get('/getEmail', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->getEmailAction($header_data);
});

$app->get('/deletePassword', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->deletePasswordAction($header_data);
});

$app->post('/changePassword', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->changePasswordAction($header_data,$app->request->getPost());
});

$app->get('/aboutSoicabile/{type}', function ($type) use ( $app ) {
    $settings = new SettingsController();
    $settings->aboutSoicabileAction($header_data,$type);
});

$app->post('/setPrivacySettings', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->setPrivacySettingsAction($header_data,$app->request->getPost());
});

$app->get('/getPrivacySettings/{type}', function ($type) use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->getPrivacySettingsAction($header_data,$type);
});

$app->notFound(
	function () use ( $app ) {
            $app->response->setStatusCode( 404, "Not Found" )->sendHeaders();
             Library::output(false, '0', "This Api not exist", null);
	}
);
$app->handle();
