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
    
    public function sendRequestAction($header_data,$post_data)
    {
        if( !isset($post_data['request_user_id']) || !isset($post_data['group_id'])) {
            Library::logging('alert',"API : sendRequest : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 2) {
                    $group_ids =  json_encode($post_data['group_id']);
                } else {
                    $group_ids =  $post_data['group_id'];
                }
                
                $result = array();
                $user = Users::findById($header_data['id']);
                if(isset($user->request_sent)) {
                    foreach($user->request_sent as $request_sent) {
                        if($post_data['request_user_id'] == $request_sent['user_id']) {
                             Library::output(false, '0', "Request Already Sent To This User.", null);
                        }
                    }
                }
                
                $db = Library::getMongo();
                //foreach($group_ids as $id) {
                    // query for request sent by the user
                    $request_sent = $db->execute('db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$push : {request_sent:{$each:[{user_id:"'.$post_data['request_user_id'].'",group_id:'.$group_ids.',is_active:0,date:"'.time().'"}]}}})');
                    if($request_sent['ok'] == 0) {
                        Library::logging('error',"API : sendRequest (request sent query) mongodb error: ".$request_sent['errmsg']." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                //}
                
                // query for request pending entry by the user
                $request_pending = $db->execute('db.users.update({"_id" :ObjectId("'.$post_data['request_user_id'].'") },{$push : {request_pending:{$each:[{user_id:"'.$header_data['id'].'",group_id:'.$group_ids.',is_active:0,date:"'.time().'"}]}}})');
                
                if($request_pending['ok'] == 0) {
                    Library::logging('error',"API : sendRequest (request_pending query) mongodb error: ".$request_pending['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                
                Library::output(true, '1', USER_REQUEST_SENT, null);
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
    
    public function pendingRequestAction($header_data)
    {
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
                        $result[$i]['user_id'] = (string)$user->_id;
                        $result[$i]['username'] = $user->username;
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
    
    public function requestAcceptAction($header_data,$post_data)
    {
        try {
             if( !isset($post_data['accept_user_id']) || !isset($post_data['group_id'])) {
                Library::logging('alert',"API : requestAccept : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_INPUT, null);
            } else {
                $db = Library::getMongo();
                
                if($header_data['os'] == 2) {
                    $group_ids =  json_encode($post_data['group_id']);
                } else {
                    $group_ids =  $post_data['group_id'];
                }
                $user = Users::findById($header_data['id']);
                $request_pending_ids = $user->request_pending;
                
                // query to accepting pending friend request & add running group
                foreach($request_pending_ids as $request_ids) {
                    if($request_ids['user_id'] == $post_data['accept_user_id']) {
                        $grp_ids = $request_ids['group_id'];  // group ids of requested user
                        //foreach($group_ids as $id) {
                           
                            $request_accept = $db->execute('db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$push : {running_groups:{$each:[{user_id:"'.$post_data['accept_user_id'].'",group_id:'.$group_ids.',date:"'.time().'"}]}}})');
                           
                            if($request_accept['ok'] == 0) {
                                Library::logging('error',"API : requestAccept (request accept query) mongodb error: ".$request_accept['errmsg']." ".": user_id : ".$header_data['id']);
                                Library::output(false, '0', ERROR_REQUEST, null);
                            }
                            break;
                        //}
                    } else {
                        Library::output(false, '0', WRONG_USER_ID, null);
                    }
                }
                
                // qeury for adding running group whom request is accept
                 $grp_ids = json_encode($grp_ids);
                 $request_sync = $db->execute('db.users.update({"_id" :ObjectId("'.$post_data['accept_user_id'].'") },{$push : {running_groups:{$each:[{user_id:"'.$header_data['id'].'",group_id:'.$grp_ids.',date:"'.time().'"}]}}})');
                 if($request_sync['ok'] == 0) {
                    Library::logging('error',"API : requestAccept (request sync query) mongodb error: ".$request_sync['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                    
                }
                
                // query for updating request sent array (is_active = 1) 
                $request_update = $db->execute('db.users.update(
                                        {"_id" : ObjectId("'.$post_data['accept_user_id'].'"),"request_sent.user_id": "'.$header_data['id'].'"}, 
                                        {$set: {
                                            "request_sent.$.is_active": 1
                                        }}
                                        )');
                 if($request_update['ok'] == 0) {
                    Library::logging('error',"API : requestAccept (updating request sent array) mongodb error: ".$request_update['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                    
                }
                // query for delete pending request after accepting
                $delete = 'db.users.update(
                            {_id:ObjectId("'.$header_data['id'].'") },
                            { $pull: { request_pending: { user_id: "'.$post_data['accept_user_id'].'" } } },
                            { multi: true }
                          )';
                $delete_pending = $db->execute($delete);
                if($delete_pending['ok'] == 0) {
                    Library::logging('error',"API : requestAccept (delete pending query) mongodb error: ".$delete_pending['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                
                Library::output(true, '1', USER_ACCEPT, null);
            }
        } catch (Exception $e) {
            Library::logging('error',"API : requestAccept : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
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
    
    public function getFriendsAction($header_data)
    {
        try {
            $friends_list = array();
            $user = Users::findById($header_data['id']);
            
            $i=0;
            if(isset($user->running_groups)) {
                foreach($user->running_groups as $user_ids) {
                    $friends_info = Users::findById($user_ids['user_id']);
                    $friends_list[$i]['friends_id'] = (string)$friends_info->_id;
                    $friends_list[$i]['username'] = $friends_info->username;
                    $friends_list[$i]['group_id'] = $user_ids['group_id'];
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
