<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FriendsController 
{
    /**
     * Method for new user registration
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
                if($header_data['os'] == 1) {
                    $group_ids =  json_decode($post_data['group_id']);
                } else {
                    $group_ids =  $post_data['group_id'];
                }
                $result = array();
                $user = Users::findById($header_data['id']);
                
                $db = Library::getMongo();
                foreach($group_ids as $id) {
                    // query for request sent by the user
                    $comments = $db->execute('db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$push : {request_sent:{$each:[{user_id:"'.$post_data['request_user_id'].'",group_id:"'.$id.'",is_active:0,date:"'.time().'"}]}}})');
                }
                
                // query for request accept by the user
                $comments = $db->execute('db.users.update({"_id" :ObjectId("'.$post_data['request_user_id'].'") },{$push : {request_pending:{$each:[{user_id:"'.$header_data['id'].'",is_active:0,date:"'.time().'"}]}}})');
                
                Library::output(true, '1', USER_REQUEST_SENT, null);
            } catch(Exception $e) {
                Library::logging('error',"API : sendRequest : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method for new user registration
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
        } catch(Exception $e) {
            Library::logging('error',"API : pendingRequest : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
}

?>
