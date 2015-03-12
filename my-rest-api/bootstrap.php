<?php

define("TESTING", true);

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
define('USER_ACCEPT', 'Request Accepted Successfully.');
define('USER_REJECT', 'Request Rejected Successfully.');
define('WRONG_USER_ID', 'Wrong User Id To Accept.');
define('CHANGE_NUMBER', 'Phone Number Change Successfully.');
define('GROUP_ADDED', 'Group Added Successfully.');
define('SET_PASSWORD', 'Password set successfully.');
define('DELETE_PASSWORD', 'Password deleted successfully.');
define('PRIVACY_SETTINGS', 'Settings Saved successfully.');
define('SHARE_IMAGE', 'Image Share successfully.');
define('INVALID_ID', 'Invalid User Id.');
define('IMAGE_UPLOAD', 'Image Uploaded Sucessfully.');
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


// otp relaed messages
define('OTP_SENT', 'OTP Sent Successfully');
define('OTP_VERIFIED', 'OTP Verified.');
define('OTP_WRONG', 'Please enter valid verification code.');

// authentication
define('KEY', 'JUTdqn7yMq5BjrQoiDo6kbYHymcoaWmbR5mlbEt');
define('TOKEN_MSG','Token Generated Successfully.');
define('TOKEN_WRONG','Token Mismatch.');
define('WRONG_OS_VERSION','Wrong Os OR Version');

// post & comment related messagess
define('POST_SAVED', 'Post Saved Successfully.');
define('COMMENT_SAVED', 'Comment Saved Successfully.');
define('POST_LIKED', 'Post Liked Successfully.');
define('POST_DISLIKED', 'Post Disliked Successfully.');
define('POST_ALREADY_LIKED', 'Post Already Liked By User');
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
define('AUTHKEY','AKIAJXUZE7L54DA6Y3NA');
define('SECRETKEY','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');
define('S3BUCKET','newchatejabberd');
define('SUCCESS_ACTION_REDIRECT','http://54.164.91.58/getStatus');
//define('SUCCESS_ACTION_REDIRECT','http://192.168.0.60/sociabileapi/my-rest-api/getStatus');

define('FORM_ACTION','http://newchatejabberd.s3.amazonaws.com/'); 
define('ACL','public-read');
//define('CDN_URL','cgintelmob.cafegive.com');
define('TOKEN_EXP_DURATION','58');  //seconds

// jaxl constants
define('JAXL_HOST_NAME','192.168.0.37');
//define('JAXL_HOST_NAME','kelltontech.biz');
define('JAXL_REG_FAILED', "User's Registration On Chat Server Failed"); 
define('JAXL_DISCONNECTED', "Chat Server Connection Interrupted"); 
define('JAXL_AUTH_FAILURE', "Invalid User Credentials For Chat Server"); 
define('JAXL_MUC_NOT_FOUND', "This Chat Group Does Not Exists"); 
define('JAXL_ERR_JOIN_MUC', "Some Error Occurred While Joining the Chat Group"); 
define('JAXL_MUC_JOINED', "User Successfully Joined the Chat Group"); 
define('JAXL_MUC_EXISTS', "Chat Group With This Name Already Exists"); 
define('JAXL_ERR_CREATE_MUC', "Some Error Occurred While Creating the Chat Group"); 
define('JAXL_MUC_CREATED', "Chat Group Successfully Created and Joined By User"); 
define('JAXL_NOT_A_MUC_MEMBER', "You are not a member of this group."); 
define('JAXL_NO_MUC_MEMBER', "Atleast one member should be selected."); 
