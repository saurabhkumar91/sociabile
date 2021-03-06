<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CommentsController 
{ 
    /**
     * Method for posting comment 
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function postCommentsAction($header_data,$post_data) {
        if(!isset($post_data['post_id']) || !isset($post_data['comment'])) {
            Library::logging('alert',"API : postComments : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $result = array();
                $post = Posts::findById($post_data['post_id']);
                $user = Users::findById($header_data['id']);
                if($post->_id) {
                    $comment = new Comments();
                    $comment->post_id = $post_data['post_id'];
                    $comment->user_id = $header_data['id'];
                    $comment->comment_text = $post_data['comment'];
                    $comment->date = time();
                    if ($comment->save() == false) {
                        foreach ($comment->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : postComments : ".$errors." user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    } else {
                        $post->total_comments = $post->total_comments+1;
                        $post->save();
                        $result['username']             = $user->username;
                        $result['comment_id']           = (string)$comment->_id;
                        $result['comment_text']         = $post_data['comment'];
                        $result['post_id']              = $comment->post_id;
                        $result['comment_timestamp']    = $comment->date;
                        $result['profile_pic']          = FORM_ACTION.$user->profile_image;
                        
                        if( $post->user_id != $header_data["id"]){
                            $db     = Library::getMongo();
                            $res    = $db->execute('return db.users.find( { "_id" : ObjectId("'.$post->user_id.'") }, {} ).toArray()');
                            if( $res['ok'] == 0 ){
                                Library::logging('error',"API : postComments, mongodb error: ".$res['errmsg']." : user_id : ".$header_data["id"]);
                                Library::output(false, '0', ERROR_REQUEST, null);
                            }
                            if( !empty($res['retval'][0]["os"]) && in_array($res['retval'][0]["os"], array("1", "2")) && !empty($res['retval'][0]["device_token"]) ){
                                $postType   = ($post->type==2 || $post->type==3) ? "photo" : "my mind";
                                //$post       = new PostsController();
                               // $postDetail = $post->getPostDetail( $header_data["id"], $post_data['post_id'] );
                                $message    = array( "message"=>$user->username/*." (".$user->mobile_no.")"*/." commented on your $postType.", "type"=>NOTIFY_COMMENT_RECEIVED, "post_type"=>$post->type, "post_id"=>$post_data['post_id'] );
                                $sendTo     = ($res['retval'][0]["os"] == "1") ? "android" : "ios";
                                $settings   = new SettingsController();
                                $settings->sendNotifications( array($res['retval'][0]["device_token"]), array("message"=>json_encode($message)), $sendTo );
                            }
                        }
                        Library::output(true, '1', COMMENT_SAVED, $result);
                    }
                }
               
            } catch (Exception $e) {
                Library::logging('error',"API : postComments : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    /**
     * Method for posting comment 
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getCommentsAction($header_data,$post_id) {
         if(empty($post_id)) {
            Library::logging('alert',"API : getComments : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user       = Users::findById( $header_data["id"] );
                $db         = Library::getMongo();
                $comments   = $db->execute('var comments = [] ;
                db.comments.find({"post_id":"'.$post_id.'"}).forEach(
                function (newComments) { 
                    newComments.user = db.users.findOne({"_id":ObjectId(newComments.user_id)},{username:1,profile_image:1, is_deleted:1});
                    comments.push(newComments);
                    }
                    ); 
                    return comments;
                ');
               
                if(count($comments['retval'])> 0) {
                    $friends    = array( $header_data['id']=>$header_data['id'] );
                    if(isset($user->running_groups)) {
                        foreach( $user->running_groups as $friend ) {
                            $friends[$friend["user_id"]]    = $friend["user_id"];
                        }
                    }
                    $i          =   0;
                    $listing    = array();
                    foreach ($comments['retval'] as $comment) {
                        if( !empty($comment["shared_with"]) && $comment['user_id'] !=$header_data['id'] ){
                            if( !in_array($header_data['id'], $comment["shared_with"]) ){
                                continue;
                            }
                        }
                        $listing[$i]['comment_id'] = (string)$comment['_id'];
                        $listing[$i]['comment_text'] = $comment['comment_text'];
                        $listing[$i]['comment_timestamp'] = $comment['date'];
                        if( empty($friends[$comment['user_id']]) || $comment['user']['is_deleted'] == 1 ){
                            $listing[$i]['user_id']     = '';
                            $listing[$i]['username']    = 'user';
                            $listing[$i]['profile_pic'] = FORM_ACTION.DEFAULT_PROFILE_IMAGE;
                        }else{
                            $listing[$i]['user_id']     = $comment['user_id'];
                            $listing[$i]['username']    = isset($comment['user']['username']) ? $comment['user']['username'] : 'user';
                            $listing[$i]['profile_pic'] = FORM_ACTION.$comment['user']['profile_image'];
                        }
                        $i++;
                    }
                    usort($listing, function($commentA, $commentB){
                        if ($commentA["comment_timestamp"] == $commentB["comment_timestamp"]) {
                            return 0;
                        }
                        return ($commentA["comment_timestamp"] < $commentB["comment_timestamp"]) ? 1 : -1;
                    });       
                    $result['comments'] = $listing;
                    Library::output(true, '1', "No Error", $result);
                } else {
                    $result['comments'] = array();
                    Library::output(true, '1', "No Error", $result);
                }
            } catch (Exception $e) {
                Library::logging('error',"API : getComments : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    public function setCommentPrivacyAction( $header_data, $post_data ){
        if(!isset($post_data['comment_id']) && !isset($post_data['user_id']) ) {
            Library::logging('alert',"API : setCommentPrivacy : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 1) {
                    $post_data['user_id'] =  json_decode($post_data['user_id']);
                }else{
                    if( empty($post_data['user_id']) ){
                        $post_data['user_id'] = array();
                    }
                }
                if( !is_array($post_data['user_id']) ) {
                    Library::logging('alert',"API : setCommentPrivacy : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_INPUT, null);
                }
                $comment   = Comments::findById( $post_data['comment_id'] );
                if( $comment ){
//                    if( empty($comment->shared_with) ){
//                        $comment->shared_with   = array();
//                    }
                    $comment->shared_with   = array();
                    foreach($post_data['user_id'] AS $userId ){
                        if( !in_array( $userId, $comment->shared_with) ){
                            $comment->shared_with[]    = $userId;
                        }
                    }
                    if($comment->save()){
                        Library::output(true, '1', PRIVACY_SETTINGS, null);
                        
                    }else{
                        foreach ($comment->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : setCommentPrivacy : ".$errors." user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    }
                }else{
                    Library::logging('error',"API : setCommentPrivacy : Invalid comment Id : user_id : ".$header_data['id'].", comment_id: ".$post_data['comment_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : setCommentPrivacy : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
        
    }
    
    public function getCommentPrivacyAction( $header_data, $post_data ){
        if( !isset($post_data['comment_id']) ) {
            Library::logging('alert',"API : getCommentPrivacy : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $comment   = Comments::findById( $post_data['comment_id'] );
                if( $comment ){
                    if( empty($comment->shared_with) ){
                        $comment->shared_with   = array();
                    }
                    
                    $user       = Users::findById( $header_data["id"] );
                    $friends    = array( $header_data['id']=>$header_data['id'] );
                    if(isset($user->running_groups)) {
                        foreach( $user->running_groups as $friend ) {
                            $friends[$friend["user_id"]]    = $friend["user_id"];
                        }
                    }
                    $result = array();
                    foreach( $comment->shared_with AS $friendId){        
                        $friend         = Users::findById( $friendId );
                        if($friend){
                            if( !isset($friend->username) ){
                                $friend->username   = "";
                            }
                            if( empty($friends[$friendId]) || $friend->is_deleted == 1 ){
                                $result[]  = array( "user_id"=>"" ,"name"=> "user", "profile_image"=>FORM_ACTION.DEFAULT_PROFILE_IMAGE );
                            }else{
                                $result[]  = array( "user_id"=>$friendId, "name"=> $friend->username, "profile_image"=>FORM_ACTION.$friend->profile_image );
                            }
                        }
                    }
                    
                    Library::output(true, '1', "No error", $result);
                }else{
                    Library::logging('error',"API : getCommentPrivacy : Invalid comment Id : user_id : ".$header_data['id'].", comment_id: ".$post_data['comment_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : getCommentPrivacy : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
        
    }
}
?>
