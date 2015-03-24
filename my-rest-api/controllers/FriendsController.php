<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FriendsController 
{
    /**
     * Method for send request to user
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function sendRequestAction($header_data,$post_data){
        if( !isset($post_data['request_user_id']) || !isset($post_data['group_id'])) {
            Library::logging('alert',"API : sendRequest : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if( $post_data['request_user_id'] == $header_data['id'] ) {
                    Library::logging('error',"API : sendRequest : ".REQUEST_TO_SELF." : user_id : ".$header_data['id']);
                    Library::output(false, '0', REQUEST_TO_SELF, null);
                }
                if($header_data['os'] == 2) {
                    $groupIds =  json_encode($post_data['group_id']);
                } else {
                    $groupIds =  $post_data['group_id'];
                }
                
                $user           = Users::findById($header_data['id']);
                $requestedUser  = Users::findById($post_data['request_user_id']);
                if(isset($user->request_sent)) {
                    foreach($user->request_sent as $request_sent) {
                        if($post_data['request_user_id'] == $request_sent['user_id']) {
                            Library::output(false, '0', "Request Already Sent To This User.", null);
                        }
                    }
                }
                /******* code for subscribe(add) user on jabber server **************************************/
                require 'components/JAXL3/jaxl.php';
                $client = new JAXL(array(
                    'jid' => $user->jaxl_id,
                    'pass' => $user->jaxl_password,
                    'log_level' => JAXL_DEBUG
                ));
                $client->add_cb('on_auth_success', function() {
                    $client         = $_SESSION["client"];
                    $requestedId    = $_SESSION["requestedId"];
                    //$client->set_status("available!");  // set your status
                    $client->subscribe( $requestedId);
                    $client->send_end_stream();
                    
                });
                $client->add_cb('on_auth_failure', function() {
                    $userId = $_SESSION["userId"];
                    Library::logging('error',"API : sendRequest : ".JAXL_AUTH_FAILURE." : user_id : ".$userId);
                    Library::output(false, '0', JAXL_AUTH_FAILURE, null);
                });
                
                $client->add_cb('on_disconnect', function() {
                
                    $userId             = $_SESSION["userId"];
                    $db                 = Library::getMongo();
                    $requestedUserId    = $_SESSION["requestedUserId"];
                    $groupIds          = $_SESSION["groupIds"];
                    /**************************************code for db entry of request***************************/
                    // insert request_user_id and groups in request_sent of user
                    $request_sent = $db->execute('db.users.update({"_id" :ObjectId("'.$userId.'") },{$push : {request_sent:{$each:[{user_id:"'.$requestedUserId.'",group_id:'.$groupIds.',is_active:0,date:"'.time().'"}]}}})');
                    if($request_sent['ok'] == 0) {
                        Library::logging('error',"API : sendRequest (request sent query) mongodb error: ".$request_sent['errmsg']." ".": user_id : ".$userId);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }

                    // query for request pending entry by the user
                    $request_pending = $db->execute('db.users.update({"_id" :ObjectId("'.$requestedUserId.'") },{$push : {request_pending:{user_id:"'.$userId.'",group_id:'.$groupIds.',is_active:0,date:"'.time().'"}}})');

                    if($request_pending['ok'] == 0) {
                        Library::logging('error',"API : sendRequest (request_pending query) mongodb error: ".$request_pending['errmsg']." ".": user_id : ".$userId);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                    /**************************************code for db entry of request ends ************************/
                    $os             = $_SESSION["os"];
                    $deviceToken    = $_SESSION["deviceToken"];
                    if( in_array($os, array("1", "2")) && !empty($deviceToken) ){
                        $userMobileNo   = $_SESSION["userMobileNo"];
                        $message        = array( "message"=>"You received friend request from $userMobileNo", "type"=>NOTIFY_FRIEND_REQUEST_RECEIVED );
                        $sendTo     = ($os == "1") ? "android" : "ios";
                        $settings   = new SettingsController();
                        $settings->sendNotifications( array($deviceToken), array("message"=>json_encode($message)), $sendTo );
                    }

                    Library::output(true, '1', USER_REQUEST_SENT, null);
                });                    

                $_SESSION["client"]             = $client;
                $_SESSION["requestedId"]        = $requestedUser->jaxl_id;
                $_SESSION["os"]                 = empty($requestedUser->os) ? '' : $requestedUser->os ;
                $_SESSION["deviceToken"]        = empty($requestedUser->device_token) ? '' : $requestedUser->device_token;
                $_SESSION["userMobileNo"]       = $user->mobile_no;
                $_SESSION["userId"]             = $header_data['id'];
                $_SESSION["requestedUserId"]    = $post_data['request_user_id'];
                $_SESSION["groupIds"]           = $groupIds;
                $client->start();
                /******* code for subscribe(add) user end **************************************/
                    
                
            } catch(Exception $e) {
                Library::logging('error',"API : sendRequest : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method for listing of pending request
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function pendingRequestAction($header_data){
        try {
            $result = array();
            $i = 0;
            $db = Library::getMongo();
            $get_users = $db->execute('return db.users.aggregate([
                                    { $match: { _id: ObjectId("'.$header_data['id'].'") } },
                                        { $unwind: "$request_pending" },
                                        { $group: { _id: "$request_pending.user_id"  } }
                                    ]).toArray();');
            if($get_users['ok'] == 1) {
                 if(is_array($get_users['retval'])) {
                    foreach($get_users['retval'] as $info) {
                        $user = Users::findById($info['_id']);
                        $result[$i]['user_id']  = (string)$user->_id;
                        $result[$i]['username'] = $user->username;
                        $result[$i]['jaxl_id']  = $user->jaxl_id;
                        $result[$i]['profile_image'] = isset($user->profile_image) ? FORM_ACTION.$user->profile_image : 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg';
                        $i++;
                    }
                    Library::output(true, '1', "No Error", $result);
                } else {
                    Library::output(true, '1', "No Error", $result);
                }
            } else {
                Library::output(false, '0', ERROR_REQUEST, null);
                Library::logging('error',"API : pendingRequest mongodb error: ".$get_users['errmsg']." ".": user_id : ".$header_data['id']);
            }
           
        } catch(Exception $e) {
            Library::logging('error',"API : pendingRequest : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for accepting friend request
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function requestAcceptAction($header_data,$post_data){
        try {
             if( !isset($post_data['accept_user_id']) || !isset($post_data['group_id'])) {
                Library::logging('alert',"API : requestAccept : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_INPUT, null);
            } else {
                
                if($header_data['os'] == 2) {
                    $groupIds =  json_encode($post_data['group_id']);
                } else {
                    $groupIds =  $post_data['group_id'];
                }
                $user           = Users::findById($header_data['id']);
                $acceptUser     = Users::findById($post_data['accept_user_id']);
                $request_pending_ids = $user->request_pending;
                
                $userDetails                = array();
                $userDetails['friends_id']  = $header_data['id'];
                $userDetails['username']    = $user->username;
                $userDetails['group_id']    = json_decode($groupIds);
                $userDetails['jaxl_id']     = $user->jaxl_id;
                $userDetails['profile_pic'] = FORM_ACTION.$user->profile_image;
                
                $requestFound   = false;
                foreach($request_pending_ids as $request_ids) {
                    if($request_ids['user_id'] == $post_data['accept_user_id']) {
                        $requestGroups  = $request_ids['group_id'];  // group ids of requested user
                        $requestFound   = true;
                        break;
                    } 
                }
                if( !$requestFound ) {
                    Library::output(false, '0', WRONG_USER_ID, null);
                }
                
                /******* code for subscribe(add) user on jabber server **************************************/
                require 'components/JAXL3/jaxl.php';
                $client = new JAXL(array(
                    'jid' => $user->jaxl_id,
                    'pass' => $user->jaxl_password,
                    'log_level' => JAXL_DEBUG
                ));
                $client->add_cb('on_auth_success', function() {
                    $client         = $_SESSION["client"];
                    $acceptId    = $_SESSION["acceptId"];
                    //$client->set_status("available!");  // set your status
                    $client->subscribed( $acceptId);
                    $client->send_end_stream();
                });
                $client->add_cb('on_auth_failure', function() {
                    $userId = $_SESSION["userId"];
                    Library::logging('error',"API : requestAccept : ".JAXL_AUTH_FAILURE." : user_id : ".$userId);
                    Library::output(false, '0', JAXL_AUTH_FAILURE, null);
                });
                
                $client->add_cb('on_disconnect', function() {
                    $db             = Library::getMongo();
                    $userId         = $_SESSION["userId"];
                    $acceptUserId   = $_SESSION["accept_user_id"];
                    $groupIds       = $_SESSION["groupIds"];
                    $requestGroups  = json_encode($_SESSION["requestGroups"]);
                    $request_accept = $db->execute('db.users.update({"_id" :ObjectId("'.$userId.'") },{$push : {running_groups:{$each:[{user_id:"'.$acceptUserId.'",group_id:'.$groupIds.',date:"'.time().'"}]}}})');

                    if($request_accept['ok'] == 0) {
                        Library::logging('error',"API : requestAccept (request accept query) mongodb error: ".$request_accept['errmsg']." ".": user_id : ".$userId);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                
                
                    // qeury for adding running group whom request is accept
                    $request_sync = $db->execute('db.users.update({"_id" :ObjectId("'.$acceptUserId.'") },{$push : {running_groups:{$each:[{user_id:"'.$userId.'",group_id:'.$requestGroups.',date:"'.time().'"}]}}})');
                    if($request_sync['ok'] == 0) {
                        Library::logging('error',"API : requestAccept (request sync query) mongodb error: ".$request_sync['errmsg']." ".": user_id : ".$userId);
                        Library::output(false, '0', ERROR_REQUEST, null);

                    }

                    // query for updating request sent array (is_active = 1) 
                    $request_update = $db->execute('db.users.update(
                                            {"_id" : ObjectId("'.$acceptUserId.'"),"request_sent.user_id": "'.$userId.'"}, 
                                            {$set: {
                                                "request_sent.$.is_active": 1
                                            }}
                                            )');
                     if($request_update['ok'] == 0) {
                        Library::logging('error',"API : requestAccept (updating request sent array) mongodb error: ".$request_update['errmsg']." ".": user_id : ".$userId);
                        Library::output(false, '0', ERROR_REQUEST, null);

                    }
                    // query for delete pending request after accepting
                    $delete = 'db.users.update(
                                {_id:ObjectId("'.$userId.'") },
                                { $pull: { request_pending: { user_id: "'.$acceptUserId.'" } } },
                                { multi: true }
                              )';
                    $delete_pending = $db->execute($delete);
                    if($delete_pending['ok'] == 0) {
                        Library::logging('error',"API : requestAccept (delete pending query) mongodb error: ".$delete_pending['errmsg']." ".": user_id : ".$userId);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                    
                    $os             = $_SESSION["os"];
                    $deviceToken    = $_SESSION["deviceToken"];
                    if( in_array($os, array("1", "2")) && !empty($deviceToken) ){
                        $userMobileNo   = $_SESSION["userMobileNo"];
                        $userDetails    = $_SESSION["userDetails"];
                        $message        = array( "message"=>"$userMobileNo accepted your friend request.", "type"=>NOTIFY_FRIEND_REQUEST_ACCEPTED, "userDetails" => $userDetails );
                        $sendTo     = ($os == "1") ? "android" : "ios";
                        $settings   = new SettingsController();
                        $settings->sendNotifications( array($deviceToken), array("message"=>json_encode($message)), $sendTo );
                    }
                    
                    Library::output(true, '1', USER_ACCEPT, null);
                });                    

                $_SESSION["client"]         = $client;
                $_SESSION["acceptId"]       = $acceptUser->jaxl_id;
                $_SESSION["userId"]         = $header_data['id'];
                $_SESSION["accept_user_id"] = $post_data['accept_user_id'];
                $_SESSION["groupIds"]       = $groupIds;
                $_SESSION["requestGroups"]  = $requestGroups;
                $_SESSION["os"]             = empty($acceptUser->os) ? '' : $acceptUser->os ;
                $_SESSION["deviceToken"]    = empty($acceptUser->device_token) ? '' : $acceptUser->device_token;
                $_SESSION["userMobileNo"]   = $user->mobile_no;
                $_SESSION["userDetails"]   = $userDetails;
                
                $client->start();
                /******* code for subscribe(add) user end **************************************/
            }
        } catch (Exception $e) {
            Library::logging('error',"API : requestAccept : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    /**
     * Method for rejecting friend request
     *
     * @param $header_data: user and device details
     * @param $post_data: post request data array containing:
     * - accept_user_id: id of user whose request has to be rejected
     * @author Saurabh kumar
     * @return json
     */
    
    public function rejectRequestAction($header_data,$post_data){
        try {
            if( !isset($post_data['reject_user_id']) ) {
                Library::logging('alert',"API : requestAccept : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_INPUT, null);
            } else {
                
                $user       = Users::findById($header_data['id']);
                $rejectUser = Users::findById($post_data['reject_user_id']);
                /******* code for subscribe(add) user on jabber server **************************************/
                require 'components/JAXL3/jaxl.php';
                $client = new JAXL(array(
                    'jid'       => $user->jaxl_id,
                    'pass'      => $user->jaxl_password,
                    'log_level' => JAXL_DEBUG
                ));
                $client->add_cb('on_auth_success', function() {
                    $client      = $_SESSION["client"];
                    $rejectId    = $_SESSION["rejectId"];
                    //$client->set_status("available!");  // set your status
                    $client->unsubscribed( $rejectId);
                    $client->send_end_stream();
                });
                $client->add_cb('on_auth_failure', function() {
                    $userId = $_SESSION["userId"];
                    Library::logging('error',"API : rejectRequest : ".JAXL_AUTH_FAILURE." : user_id : ".$userId);
                    Library::output(false, '0', JAXL_AUTH_FAILURE, null);
                });
                
                $client->add_cb('on_disconnect', function() {
                        $db = Library::getMongo();
                        $userId = $_SESSION["userId"];
                        $rejectUserId = $_SESSION["reject_user_id"];
                        // query for delete pending request
                        $delete = 'db.users.update(
                                    {_id:ObjectId("'.$userId.'") },
                                    { $pull: { request_pending: { user_id: "'.$rejectUserId.'" } }, $push:{hidden_contacts:"'.$rejectUserId.'"} },
                                    { multi: true }
                                  )';
                        $delete_pending = $db->execute($delete);
                        if($delete_pending['ok'] == 0) {
                            Library::logging('error',"API : rejectRequest (delete pending query) mongodb error: ".$delete_pending['errmsg']." ".": user_id : ".$userId);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        }

                        // query for  delete sent request
                        $request_update = $db->execute('db.users.update(
                                                {"_id" : ObjectId("'.$rejectUserId.'"),"request_sent.user_id": "'.$userId.'"}, 
                                                {$pull: { request_sent: { user_id: "'.$userId.'" } } }
                                            )');
                         if($request_update['ok'] == 0) {
                            Library::logging('error',"API : rejectRequest (updating request sent array) mongodb error: ".$request_update['errmsg']." ".": user_id : ".$userId);
                            Library::output(false, '0', ERROR_REQUEST, null);

                        }
                        Library::output(true, '1', USER_REJECT, null);
                });                    

                $_SESSION["client"]         = $client;
                $_SESSION["rejectId"]       = $rejectUser->jaxl_id;
                $_SESSION["userId"]         = $header_data['id'];
                $_SESSION["reject_user_id"] = $post_data['reject_user_id'];
                $client->start();
                /******* code for subscribe(add) user end **************************************/
                
            }
        } catch (Exception $e) {
            Library::logging('error',"API : rejectRequest : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    /**
     * Method for showing friend list
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getFriendsAction($header_data){
        try {
            $friends_list = array();
            $user = Users::findById($header_data['id']);
            $i=0;
            if(isset($user->running_groups)) {
                foreach($user->running_groups as $user_ids) {
                    $friends_info = Users::findById($user_ids['user_id']);
                    if( empty($friends_info->is_active) ){
                        continue;
                    }
                    $friends_list[$i]['friends_id'] = (string)$friends_info->_id;
                    $friends_list[$i]['username'] = $friends_info->username;
                    $friends_list[$i]['group_id'] = $user_ids['group_id'];
                    $friends_list[$i]['jaxl_id'] = $friends_info->jaxl_id;
                    $friends_list[$i]['profile_image'] = isset($friends_info->profile_image) ? FORM_ACTION.$friends_info->profile_image : 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg';
                    $i++;
                }
                Library::output(true, '1', "No Error", $friends_list);
            } else {
                Library::output(true, '1', "No Error", $friends_list);
            }
            
        } catch (Exception $e) {
            Library::logging('error',"API : getFriends : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
}

?>
