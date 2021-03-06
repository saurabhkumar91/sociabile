<?php

define("TESTING", true);

define("SERVER_NAME", "http://social-1411310927.us-west-2.elb.amazonaws.com/" );

 // user related messages
define('ERROR_INPUT', 'Please provide all input values.');
define('ERROR_REQUEST', 'Error in request. Please try again');
define('USER_NOT_REGISTERED', 'Invalid User');
define('CONTACTS_SAVED', 'All The Contacts Has Been Saved Successfully.');
define('USER_NAME_SAVED', 'User Name Saved Successfully.');
define('HEADER_INFO', 'Missing Header Information.');
define('USER_PROFILE', 'Profile Saved Successfully.');
define('USER_PROFILE_IMAGE', 'Profile Image Changed Successfully.');
define('CONTEXT_INDICATOR', 'Context Indicator Set Successfully.');
define('USER_REQUEST_SENT', 'Request Sent Successfully.');
define('REQUEST_TO_SELF', 'You Can Not Send Request To Yourself.');
define('REQUEST_TO_HIDDEN', 'You Can Not Send Request To Hidden Users.');
define('USER_ACCEPT', 'Request Accepted Successfully.');
define('USER_REJECT', 'Request Rejected Successfully.');
define('WRONG_USER_ID', 'Wrong User Id To Accept.');
define('CHANGE_NUMBER', 'Phone Number Change Successfully.');
define('GROUP_ADDED', 'Group Added Successfully.');
define('GROUP_DELETE_AUTH_ERROR', 'You are not authorized to delete this group.');
define('GROUP_DELETED', 'Group deleted successfully.');
define('SET_PASSWORD', 'Password set successfully.');
define('DELETE_PASSWORD', 'Password deleted successfully.');
define('PRIVACY_SETTINGS', 'Settings saved successfully.');
define('SHARE_IMAGE', 'Image shared successfully.');
define('INVALID_ID', 'Invalid User Id.');
define('IMAGE_UPLOAD', 'Image Uploaded Successfully.');
define('WRONG_TYPE', 'Wrong Type.');
define('UNIQUE_USER_ID', 'This User Id Already Exists');
define('UNIQUE_USER_UPDATED', 'Unique User Id Updated Successfully.');
define('UNIQUE_USER_ALREADY_SET', 'Unique User Id already updated.');
define('WRONG_UNIQUE_ID', 'Unique User is not valid.');
define('NO_USER_FOUND', 'No result found');
define('DEFAULT_IMAGE', 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg');
define('DEFAULT_PROFILE_IMAGE', 'profiles/default.png');
define('USER_LOGIN', 'User Successfully Login.');
define('INVALID_LOGIN', 'User Id or Password is Incorrect.');
define('NO_PROFILE_IMAGE', 'You did not set your profile image yet.');
define('PROFILE_IMAGE_NOT_DELETED', 'Your profile image could not be deleted now. Please Try again later.');
define('PROFILE_IMAGE_DELETED', 'Your profile image has been deleted successfully.');
define('DEVICE_TOKEN_UPDATED', 'Device token updated successfully.');
define('USER_DEACTIVATED', 'Account deactivated successfully.');
define('USER_HIDDEN', 'User successfully added to hidden list.');
define('USER_UNHIDDEN', 'User successfully removed from hidden list.');
define('USER_REMOVED', 'Account removed successfully.');
define('GROUP_CHANGED', "Friend's group changed successfully.");
define('USER_UNFRIENDED', "User successfully removed from friend list.");
define('RECOVERY_MAIL_SET', "User's recovery email id updated successfully.");


// otp relaed messages
define('OTP_SENT', 'OTP Sent Successfully');
define('OTP_VERIFIED', 'OTP Verified.');
define('OTP_WRONG', 'Please enter valid verification code.');

// authentication
define('KEY', 'JUTdqn7yMq5BjrQoiDo6kbYHymcoaWmbR5mlbEt');
define('TOKEN_MSG','Token Generated Successfully.');
define('TOKEN_WRONG','Account has been recovered on another device.');
define('WRONG_OS_VERSION','Wrong Os OR Version');

// post & comment related messagess
define('POST_SAVED', 'Post Saved Successfully.');
define('COMMENT_SAVED', 'Comment Saved Successfully.');
define('POST_LIKED', 'Post Liked Successfully.');
define('POST_LIKE_REMOVED', 'Post Like Removed Successfully.');
define('POST_DISLIKE_REMOVED', 'Post Dislike Removed Successfully.');
define('POST_DISLIKED', 'Post Disliked Successfully.');
define('POST_ALREADY_LIKED', 'Post Already Liked By User');
define('POST_NOT_LIKED', 'Post Not Liked By User');
define('POST_NOT_DISLIKED', 'Post Not Disliked By User');
define('POST_ALREADY_DISLIKED', 'Post Already Disliked By User');
define('POST_DELETED', 'Post Deleted Successfully.');
define('POST_NOT_DELETED', 'Post Was Not Deleted. Plaese Try Again Later.');
define('POST_DELETE_AUTH_ERR', 'You Are Not Authorized to Delete This Post.');



// time capsule related messagess
define('TIME_CAPSULE_SAVED', 'Time Capsule Saved Successfully.');
define('TIME_CAPSULE_OPENED', 'Time Capsule Opened Successfully.');
define('TIME_CAPSULE_IMAGE', 'Time Capsule Image Saved Successfully.');
define('INVALID_CAPSULE',"Invalid Capsule Id");
define('TIME_CAPSULE_DELETED', 'Time Capsule Deleted Successfully.');
define('TIME_CAPSULE_NOT_DELETED', 'Time Capsule Was Not Deleted. Plaese Try Again Later.');
define('TIME_CAPSULE_DELETE_AUTH_ERR', 'You Are Not Authorized to Delete This Time Capsule.');
define('TIME_CAPSULE_NOT_OPENED', 'Unopened Time Capsule Can Not Be Deleted .');


//amazon variable
//define('AUTHKEY','AKIAJXUZE7L54DA6Y3NA');
//define('SECRETKEY','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');
//define('S3BUCKET','newchatejabberd');
//define('SUCCESS_ACTION_REDIRECT','http://54.164.91.58/getStatus');
//define('SUCCESS_ACTION_REDIRECT','http://192.168.0.60/sociabileapi/my-rest-api/getStatus');

//define('FORM_ACTION','http://newchatejabberd.s3.amazonaws.com/'); 
//define('ACL','public-read');
////define('CDN_URL','cgintelmob.cafegive.com');
//define('TOKEN_EXP_DURATION','58');  //seconds

define('AUTHKEY','AKIAJY5FBQ6GJZ3EKNUQ');
define('SECRETKEY','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');
define('S3BUCKET','sociabileimages');
define('SUCCESS_ACTION_REDIRECT','http://54.201.189.57/getStatus');
define('FORM_ACTION','http://sociabileimages.s3.amazonaws.com/'); 
define('ACL','public-read');
define('TOKEN_EXP_DURATION','58');  //seconds

// jaxl constants
//define('JAXL_HOST_NAME','192.168.13.142');
//define('JAXL_HOST_NAME','kelltontech.biz');
define('JAXL_HOST_NAME','sociabile-test.m.in-app.io');
define('JAXL_REG_FAILED', "User's Registration On Chat Server Failed"); 
define('JAXL_DISCONNECTED', "Chat Server Connection Interrupted"); 
define('JAXL_AUTH_FAILURE', "Invalid User Credentials For Chat Server"); 
define('JAXL_MUC_NOT_FOUND', "This Chat Group Does Not Exists"); 
define('JAXL_ERR_JOIN_MUC', "Some error occurred while joining the chat group"); 
define('JAXL_MUC_JOINED', "User successfully joined the chat group"); 
define('JAXL_MUC_EXISTS', "Chat group with this name already exists"); 
define('JAXL_ERR_CREATE_MUC', "some error occurred while creating the chat group"); 
define('JAXL_MUC_CREATED', "Chat group successfully created and joined by user"); 
define('JAXL_NOT_A_MUC_MEMBER', "You are not a member of this group."); 
define('JAXL_NO_MUC_MEMBER', "Atleast one member should be selected."); 
define('JAXL_MUC_ADD_MEMBERS_AUTH_ERROR', "You are not authorized to add memebers to this group."); 
define('JAXL_MUC_MEMBERS_ADDED', "Members successfully added to group."); 
define('JAXL_ERR_LEAVE_MUC', "Some error occurred while leaving the chat group"); 
define('JAXL_MUC_LEAVED', "Successfully leaved the Chat Group"); 
define('JAXL_DELETE_AUTH_ERR', "You are not a authorized to delete this chat group."); 
define('JAXL_ERR_DELETE_MUC', "Some error occurred while deleting the chat group."); 
define('JAXL_MUC_DELETED', "Chat group successfully deleted."); 
define('JAXL_ADMIN_ID', "admin"); 
define('JAXL_ADMIN_PASS', "12345"); 

define( 'GCM_API_ACCESS_KEY', 'AIzaSyBFBU3JBafjDBREqBFgn2m_M-FuepLZG8c' );
define( 'APN_PASSPHRASE', '123456' );

define( 'PUSH_NOTIFICATION_FAILED', 'Unable to send notifications.' );

// push notifications types
define( 'NOTIFY_FRIEND_REQUEST_RECEIVED', '1' );
define( 'NOTIFY_JOIN_GROUP_CHAT', '2' );
define( 'NOTIFY_FRIEND_REQUEST_ACCEPTED', '3' );
define( 'NOTIFY_COMMENT_RECEIVED', '4' );
define( 'NOTIFY_POST_LIKED', '5' );
define( 'NOTIFY_POST_DISLIKED', '6' );
define( 'NOTIFY_PHOTO_SHARED', '7' );
define( 'NOTIFY_BY_ADMIN', '8' );


define( 'CONTACT_US_EMAIL', "scbleHelp@gmail.com" );


// SMS constants
define( 'TWILIO_ACCOUNT_SID', "ACc9cc1ec850a70d4972c8316e6a714b0c" );
define( 'TWILIO_AUTH_TOKEN', "12c49fa03e84b7681ec5e9da564f3a6d" );
define( 'TWILIO_FROM_NUMBER', "+15052192751" );
