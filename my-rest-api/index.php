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

$app->post('/setDeviceToken', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->setDeviceTokenAction($header_data,$app->request->getPost());
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

$app->post('/setProfileImage', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->setProfileImageAction($header_data,$app->request->getPost());
});

$app->get('/deleteProfileImage', function () {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->deleteProfileImageAction($header_data);
});

$app->put('/setContextIndicator/{context}', function ($context) use ( $app ) {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->setContextIndicatorAction($header_data,$context);
});

$app->get('/deactivateAccount', function () {
    $header_data = Library::getallheaders();
    $user = new UsersController();
    $user->deactivateAccountAction($header_data);
});

$app->post('/createPost', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->createPostAction($header_data,$app->request->getPost());
});

$app->post('/getPosts', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->getPostsAction($header_data,$app->request->getPost());
});

$app->post('/likePost', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->likePostAction($header_data,$app->request->getPost());
});

$app->post('/dislikePost', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->dislikePostAction($header_data,$app->request->getPost());
});

$app->post('/postLikeDislikeDetails', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->postLikeDislikeDetailsAction($header_data,$app->request->getPost());
});

$app->post('/deletePost', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->deletePostAction($header_data,$app->request->getPost());
});

$app->post('/getPostDetails', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->getPostDetailsAction($header_data,$app->request->getPost());
});

$app->post('/removeLikePost', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->removeLikePostAction($header_data,$app->request->getPost());
});

$app->post('/removeDislikePost', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $post = new PostsController();
    $post->removeDislikePostAction($header_data,$app->request->getPost());
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

$app->get('/getStatus/{user_id}/{type}', function ($user_id,$type) use ( $app ) {
    $amazon = new AmazonsController();
    $amazon->getStatusAction($user_id,$type);
});

$app->get('/getStatus/{user_id}/{type}/{param}', function ($user_id,$type, $param) use ( $app ) {
    $amazon = new AmazonsController();
    $amazon->getStatusAction($user_id,$type, $param);
});

$app->get('/createsignature/{type}', function ($type) use ( $app ) {
    $header_data = Library::getallheaders();
    $amazon = new AmazonsController();
    $amazon->createsignatureAction($header_data,$type);
});

$app->get('/createsignature/{type}/{param}', function ($type,$param) use ( $app ) {
    $header_data = Library::getallheaders();
    $amazon = new AmazonsController();
    $amazon->createsignatureAction($header_data,$type,$param);
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

$app->post('/addGroup', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->addGroupAction($header_data,$app->request->getPost());
});

$app->post('/createChatGroup', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->createChatGroupAction( $header_data, $app->request->getPost() );
});
$app->post('/joinChatGroup', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->joinChatGroupAction( $header_data, $app->request->getPost() );
});

$app->get('/getChatGroups', function () {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->getChatGroupsAction( $header_data );
});

$app->post('/addMembersInChatGroup', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->addMembersInChatGroupAction( $header_data, $app->request->getPost() );
});

$app->post('/leaveChatGroup', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->leaveChatGroupAction( $header_data, $app->request->getPost() );
});

$app->post('/deleteChatGroup', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $group = new GroupsController();
    $group->deleteChatGroupAction( $header_data, $app->request->getPost() );
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

$app->post('/rejectRequest', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $friends = new FriendsController();
    $friends->rejectRequestAction($header_data,$app->request->getPost());
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

$app->get('/aboutSociabile/{type}', function ($type) use ( $app ) {
    $settings = new SettingsController();
    $settings->aboutSociabileAction($header_data,$type);
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

$app->post('/sharePhotos', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->sharePhotosAction($header_data,$app->request->getPost());
});

$app->get('/getFriendsInfo/{user_id}', function ($user_id) use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->getFriendsInfoAction($header_data,$user_id);
});

$app->get('/getImages/{type}', function ($type) use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->getImagesAction($header_data,$type);
});

$app->post('/uploadMultipleImages', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->uploadMultipleImagesAction( $header_data, $app->request->getPost() );
});

$app->post('/editUniqueId', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $users = new UsersController();
    $users->editUniqueIdAction($header_data,$app->request->getPost());
});

$app->get('/isSearchable/{type}', function ($type) use ( $app ) {
    $header_data = Library::getallheaders();
    $users = new UsersController();
    $users->isSearchableAction($header_data,$type);
});

$app->get('/searchUser/{unique_id}', function ($unique_id) use ( $app ) {
    $header_data = Library::getallheaders();
    $users = new UsersController();
    $users->searchUserAction($header_data,$unique_id);
});

$app->get('/isMobileSearchable/{type}', function ($type) use ( $app ) {
    $header_data = Library::getallheaders();
    $users = new UsersController();
    $users->isMobileSearchableAction($header_data,$type);
});

$app->get('/searchUserByMobile/{mobile_no}', function ($mobile_no) use ( $app ) {
    $header_data = Library::getallheaders();
    $users = new UsersController();
    $users->searchUserByMobileAction($header_data,$mobile_no);
});

$app->post('/userLogin', function () use ( $app ) {
    $header_data = Library::getallheaders();
    $settings = new SettingsController();
    $settings->userLoginAction($header_data,$app->request->getPost());
});

$app->post( "/createTimeCapsule", function() use ($app) {
    $header_data    = Library::getallheaders();
    $timeCapsule    = new TimeCapsuleController();
    $timeCapsule->createTimeCapsuleAction( $header_data, $app->request->getPost() );
} );

$app->get( "/getTimeCapsule", function() {
    $header_data    = Library::getallheaders();
    $timeCapsule    = new TimeCapsuleController();
    $timeCapsule->getTimeCapsuleAction($header_data);
} );

$app->post( "/openTimeCapsule", function() use ($app) {
    $header_data    = Library::getallheaders();
    $timeCapsule    = new TimeCapsuleController();
    $timeCapsule->openTimeCapsuleAction( $header_data, $app->request->getPost() );
} );

$app->post( "/deleteTimeCapsule", function() use ($app) {
    $header_data    = Library::getallheaders();
    $timeCapsule    = new TimeCapsuleController();
    $timeCapsule->deleteTimeCapsuleAction( $header_data, $app->request->getPost() );
} );

$app->post( "/setTimeCapsuleImages", function() use ($app) {
    $header_data    = Library::getallheaders();
    $timeCapsule    = new TimeCapsuleController();
    $timeCapsule->setTimeCapsuleImagesAction( $header_data, $app->request->getPost() );
} );

$app->notFound(
	function () use ( $app ) {
            $app->response->setStatusCode( 404, "Not Found" )->sendHeaders();
             Library::output(false, '0', "This Api not exist", null);
	}
);
$app->handle();
