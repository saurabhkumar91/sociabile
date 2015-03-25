<?php

class PostsController 
{ 
    /**
     * Method for creating post
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function createPostAction($header_data,$post_data){ 
        if(!isset($post_data['post'])) {
            Library::logging('alert',"API : createPost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $result                 = array();
                $post                   = new Posts();
                $post->user_id          = $header_data['id'];
                $post->text             = $post_data['post'];
                $post->total_comments   = 0;
                $post->likes            = 0;
                $post->dislikes         = 0;
                $post->date             = time();
                $post->type             = 1;    // type| 1 for text posts, 2 for images
                if ($post->save() == false) {
                    foreach ($post->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : createPost : ".$errors." user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                } else {
                    $result['post_id']              = (string)$post->_id;
                    $result['post_text']            = $post->text;
                    $result['post_comment_count']   = 0;
                    $result['post_like_count']      = 0;
                    $result['post_dislike_count']   = 0;
                    $result['post_timestamp']       = $post->date;
                    Library::output(true, '1', POST_SAVED, $result);
                }
            } catch (Exception $e) {
                Library::logging('error',"API : createPost : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    function getPostDetail( $userId, $postId ){
            $post       = Posts::findById( $postId );
            $user       = Users::findById( $post->user_id );
            $isLiked    = false;
            $isDisliked = false;
            if( !empty($post->liked_by) && in_array( $userId, $post->liked_by) ){
                $isLiked    = true;
            }
            if( !empty($post->disliked_by) && in_array( $userId, $post->disliked_by) ){
                $isDisliked = true;
            }
            $result["post_id"]              = (string)$post->_id;
            $result["user_id"]              = (string)$user->_id;
            $result["user_name"]            = $user->user_name;
            $result["user_profile_image"]   = FORM_ACTION.$user->profile_image;
            $result["text"]                 = ($post->type=="1") ? $post->text : '';
            $result["image"]                = array();
            $result["date"]                 = $post->date;
            $result["likes"]                = $post->likes;
            $result["dislikes"]             = $post->dislikes;
            $result["total_comments"]       = $post->total_comments;
            $result["is_liked"]             = $isLiked;
            $result["is_disliked"]          = $isDisliked;
            $result["post_type"]            = $post->type; // type| 1 for text posts, 2 for images ,3 for group of images
            $result["multiple"]             = 0;
            if( is_array($post->text) ){
                $db                 = Library::getMongo();
                $result["multiple"] = 1;
                foreach ($post->text AS $childPostId ){
                    $childPostRes   = $db->execute('return db.posts.find({ "_id" : ObjectId("'.$childPostId.'") }).toArray()');
                    if($childPostRes['ok'] == 0) {
                        Library::logging('error',"API : getPostDetails , mongodb error: ".$childPostRes['errmsg']." ".": user_id : ".$userId);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                    $childPost  = $childPostRes["retval"][0];
                    $isLiked    = false;
                    $isDisliked = false;
                    if( !empty($childPost["liked_by"]) && in_array( $userId, $childPost["liked_by"]) ){
                        $isLiked    = true;
                    }
                    if( !empty($childPost["disliked_by"]) && in_array( $userId, $childPost["disliked_by"]) ){
                        $isDisliked = true;
                    }
                    $res["post_id"]             = (string)$childPost["_id"];
                    $res["user_id"]             = (string)$user->_id;
                    $res["user_name"]           = $user->user_name;
                    $res["user_profile_image"]  = FORM_ACTION.$user->profile_image;
                    $res["text"]                = ($childPost["type"]=="1") ? $childPost["text"] : '';
                    $res["image"]               = ($childPost["type"]=="2") ? array(FORM_ACTION.$childPost["text"]):'';
                    $res["date"]                = $childPost["date"];
                    $res["likes"]               = $childPost["likes"];
                    $res["dislikes"]            = $childPost["dislikes"];
                    $res["total_comments"]      = $childPost["total_comments"];
                    $res["is_liked"]            = $isLiked;
                    $res["is_disliked"]         = $isDisliked;
                    $res["post_type"]           = $childPost["type"];
                    $res["multiple"]            = 0;
                    $result["image"][]          = $res;
                }
            }else{
                $result["image"][]  = $result;
            }
            return $result;
    }
    
    /**
     * Method posts listing
     * @param $header_data: user and device details
     * @param $post_data: post request data array containing:
     * - groups: for which posts would be serched
     * @author Saurabh Kumar
     * @return json
     */
    
    public function getPostsAction($header_data,$post_data){
        if( !isset($post_data['groups'])  ) {
            Library::logging('alert',"API : createPost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 1) {
                    $post_data["groups"] =  json_decode($post_data["groups"]);
                }
                if(!is_array($post_data['groups']) ) {
                    Library::logging('alert',"API : createPost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_INPUT, null);
                }
                $user = Users::findById($header_data['id']);
                if( !isset($user->username) ){
                    $user->username   = "";
                }
                if( !isset($user->profile_image) ){
                    $user->profile_image  = "";
                } 
                $friends    = array( 
                                $header_data['id']=>array(
                                        "name"=>$user->username, 
                                        "profile_image"=>$user->profile_image, 
                                        "type"=>"[1,2]") 
                    );
                if(isset($user->running_groups)) {
                    foreach($user->running_groups as $user_ids) {
                        // get groups in which user has added friend and are selected
                        $groupsToSearch = array_intersect($user_ids['group_id'], $post_data['groups']);
                        if( count($groupsToSearch) > 0 ) {
                            
                            $friend         = Users::findById( $user_ids['user_id'] );
                            
                            // $friendsGroup will contain the groups in which friend has put the user
                            $friendsGroup   = array();
                            foreach($friend->running_groups as $grps) {
                                    if( $grps["user_id"] == $header_data['id'] ){
                                        $friendsGroup   = $grps["group_id"];
                                        break;
                                    }
                                            
                            }
                            $type   = 0;   // user is not in any of the groups
                            // check if user lies in the my mind groups of friend
                            if( !empty($friend->my_mind_groups) && count(array_intersect($friendsGroup, $friend->my_mind_groups))){
                                $type   = 1; // user is in my mind groups
                            }
                            // check if user lies in the my pictures groups of friend
                            if( !empty($friend->my_pictures_groups) && count(array_intersect($friendsGroup, $friend->my_pictures_groups)) ){
                                if( $type == 1 ){
                                    $type   = 3; // user is in both my mind groups and my pictures groups
                                }else{
                                    $type   = 2; // user is in my pictures groups
                                }
                            }                          
                            if( $type ){
                                if( !isset($friend->username) ){
                                    $friend->username   = "";
                                }
                                $friendId                               = (string)$friend->_id;
                                $friends[$friendId]["name"]             = $friend->username;
                                $friends[$friendId]["profile_image"]    = $friend->profile_image;
                                $friends[$friendId]["type"]             = ($type==3) ? "[1,2]" : "[$type]";
                            }
                        }
                    }
                }
                $result     = array();
                $db         = Library::getMongo();
                $postGroups = array(); // posts having multiple images
                foreach( $friends AS $friendId=>$friend ){
                    $post = $db->execute('return db.posts.find({ user_id:"'.$friendId.'", type:{$in:'.$friend["type"].'} }).toArray()');
                    if($post['ok'] == 0) {
                        Library::logging('error',"API : getImages (get user info) , mongodb error: ".$post['errmsg']." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }    
                    foreach( $post['retval'] As $postDetail ){
                        $isLiked    = false;
                        $isDisliked = false;
                        if( !empty($postDetail["liked_by"]) && in_array( $header_data['id'], $postDetail["liked_by"]) ){
                            $isLiked    = true;
                        }
                        if( !empty($postDetail["disliked_by"]) && in_array( $header_data['id'], $postDetail["disliked_by"]) ){
                            $isDisliked = true;
                        }
                        $postDetail["text"] = (!is_array($postDetail["text"])&&$postDetail["type"]==2) ? FORM_ACTION.$postDetail["text"] : $postDetail["text"];
                        $postId = (string)$postDetail["_id"];
                        $result[$postId]["post_id"]             = (string)$postDetail["_id"];
                        $result[$postId]["user_id"]             = $friendId;
                        $result[$postId]["user_name"]           = $friend["name"];
                        $result[$postId]["user_profile_image"]  = FORM_ACTION.$friend["profile_image"];
                        $result[$postId]["text"]                = ($postDetail["type"]=="1") ? $postDetail["text"] : '';
                        $result[$postId]["image"]               = ($postDetail["type"]=="2") ? $postDetail["text"] : '';
                        $result[$postId]["date"]                = $postDetail["date"];
                        $result[$postId]["likes"]               = $postDetail["likes"];
                        $result[$postId]["dislikes"]            = $postDetail["dislikes"];
                        $result[$postId]["total_comments"]      = $postDetail["total_comments"];
                        $result[$postId]["is_liked"]            = $isLiked;
                        $result[$postId]["is_disliked"]         = $isDisliked;
                        $result[$postId]["post_type"]           = $postDetail["type"]; // type| 1 for text posts, 2 for images ,3 for group of images
                        $result[$postId]["multiple"]            = 0;
                        if( is_array($postDetail["text"]) ){
                            $postGroups[$postId] = $postDetail["text"];
                            $result[$postId]["multiple"]        = 1;
                        }else{
                            if( $postDetail["type"] == 2 ){
                                $result[$postId]["image"]    =  array($result[$postId]);
                            }else{
                                $result[$postId]["image"]   = array();
                            }
                        }
                    }
                }
                // for multiplpe image posts
                foreach( $postGroups As $postId=>$postGroup ){
                    $result[$postId]["image"]   = array();
                    foreach( $postGroup as $childPost ){
                        if( isset($result[$childPost]) ){
                            $result[$postId]["image"][]  = $result[$childPost]["image"][0];
                            unset( $result[$childPost] );
                        }
                    }
                }
                usort($result, function($postA, $postB){
                    if ($postA["date"] == $postB["date"]) {
                        return 0;
                    }
                    return ($postA["date"] < $postB["date"]) ? 1 : -1;
                });       
                Library::output(true, '1', "No Error", $result);

            } catch (Exception $e) {
                Library::logging('error',"API : createPost : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method to like a post
     * @param $header_data: user and device details
     * @param $post_data: post request data containing:
     * - post_id: which is being liked
     * @author Saurabh Kumar
     * @return json
     */
    
    public function likePostAction( $header_data, $post_data ){
        if(!isset($post_data['post_id'])) {
            Library::logging('alert',"API : likePost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    $post->likes        += 1;
                    if( empty($post->liked_by) ){
                        $post->liked_by = array();
                    }
                    if( in_array( $header_data['id'], $post->liked_by) ){
                        Library::logging('error',"API : likePost : ".POST_ALREADY_LIKED." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', POST_ALREADY_LIKED, null);
                    }
                    $post->liked_by[]   = $header_data['id'];
                    if($post->save()){
                        if( $post->user_id != $header_data["id"]){
                            $user   = Users::findById($header_data['id']);
                            $db     = Library::getMongo();
                            $res    = $db->execute('return db.users.find( { "_id" : ObjectId("'.$post->user_id.'") }, {} ).toArray()');
                            if( $res['ok'] == 0 ){
                                Library::logging('error',"API : likePost, mongodb error: ".$res['errmsg']." : user_id : ".$header_data["id"]);
                                Library::output(false, '0', ERROR_REQUEST, null);
                            }

                            if( !empty($res['retval'][0]["os"]) && in_array($res['retval'][0]["os"], array("1", "2")) && !empty($res['retval'][0]["device_token"]) ){
                                $postType   = ($post->type==2 || $post->type==3) ? "photo" : "my mind";
                               // $postDetail = $this->getPostDetail( $header_data["id"], $post_data['post_id'] );
                                $message    = array( "message"=>$user->mobile_no." liked your $postType.", "type"=>NOTIFY_POST_LIKED, "post_type"=>$post->type, "post_id"=>$post_data['post_id'] );
                                $sendTo     = ($res['retval'][0]["os"] == "1") ? "android" : "ios";
                                $settings   = new SettingsController();
                                $settings->sendNotifications( array($res['retval'][0]["device_token"]), array("message"=>json_encode($message)), $sendTo );
                            }
                        }
                        Library::output(true, '1', POST_LIKED, null);
                        
                    }else{
                        foreach ($post->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : likePost : ".$errors." user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    }
                }else{
                    Library::logging('error',"API : likePost : Invalid Post Id : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : likePost : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method to dislike a post
     * @param $header_data: user and device details
     * @param $post_data: post request data containing:
     * - post_id: which is being liked
     * @author Saurabh Kumar
     * @return json
     */
    
    public function dislikePostAction( $header_data, $post_data ){
        if(!isset($post_data['post_id'])) {
            Library::logging('alert',"API : dislikePost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    $post->dislikes    += 1;
                    if( empty($post->disliked_by) ){
                        $post->disliked_by = array();
                    }
                    if( in_array( $header_data['id'], $post->disliked_by) ){
                        Library::logging('error',"API : dislikePost : ".POST_ALREADY_DISLIKED." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', POST_ALREADY_DISLIKED, null);
                    }
                    $post->disliked_by[]    = $header_data['id'];
                    
                    if($post->save()){
                        if( $post->user_id != $header_data["id"]){
                            $user   = Users::findById($header_data['id']);
                            $db     = Library::getMongo();
                            $res    = $db->execute('return db.users.find( { "_id" : ObjectId("'.$post->user_id.'") }, {} ).toArray()');
                            if( $res['ok'] == 0 ){
                                Library::logging('error',"API : dislikePost, mongodb error: ".$res['errmsg']." : user_id : ".$header_data["id"]);
                                Library::output(false, '0', ERROR_REQUEST, null);
                            }

                            if( !empty($res['retval'][0]["os"]) && in_array($res['retval'][0]["os"], array("1", "2")) && !empty($res['retval'][0]["device_token"]) ){
                                $postType   = ($post->type==2 || $post->type==3) ? "photo" : "my mind";
                                //$postDetail = $this->getPostDetail( $header_data["id"], $post_data['post_id'] );
                                $message    = array( "message"=>$user->mobile_no." liked your $postType.", "type"=>NOTIFY_POST_DISLIKED, "post_type"=>$post->type, "post_id"=>$post_data['post_id'] );
                                $sendTo     = ($res['retval'][0]["os"] == "1") ? "android" : "ios";
                                $settings   = new SettingsController();
                                $settings->sendNotifications( array($res['retval'][0]["device_token"]), array("message"=>json_encode($message)), $sendTo );
                            }
                        }
                        Library::output(true, '1', POST_DISLIKED, null);
                        
                    }else{
                        foreach ($post->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : dislikePost : ".$errors." user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    }
                }else{
                    Library::logging('error',"API : dislikePost : Invalid Post Id : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : dislikePost : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method to dislike a post
     * @param $header_data: user and device details
     * @param $post_data: post request data containing:
     * - post_id: which is being liked
     * @author Saurabh Kumar
     * @return json
     */
    
    public function postLikeDislikeDetailsAction( $header_data, $post_data ){
        if(!isset($post_data['post_id'])) {
            Library::logging('alert',"API : dislikePost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user       = Users::findById( $header_data["id"] );
                $friends    = array( $header_data['id']=>$header_data['id'] );
                if(isset($user->running_groups)) {
                    foreach( $user->running_groups as $friend ) {
                        $friends[$friend["user_id"]]    = $friend["user_id"];
                    }
                }
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    if( empty($post->liked_by) ){
                        $post->liked_by = array();
                    }
                    $likedBy    = array();
                    foreach( $post->liked_by AS $friendId){        
                        $friend         = Users::findById( $friendId );
                        if($friend){
                            if( !isset($friend->username) ){
                                $friend->username   = "";
                            }
                            if( empty($friends[$friendId]) || $friend->is_deleted == 1 ){
                                $likedBy[]  = array( "user_id"=>"" ,"name"=> "user", "profile_image"=>FORM_ACTION.DEFAULT_PROFILE_IMAGE );
                            }else{
                                $likedBy[]  = array( "user_id"=>$friendId, "name"=> $friend->username, "profile_image"=>FORM_ACTION.$friend->profile_image );
                            }
                        }
                    }
                    if( empty($post->disliked_by) ){
                        $post->disliked_by = array();
                    }
                    $dislikedBy = array();
                    foreach( $post->disliked_by AS $friendId){        
                        $friend         = Users::findById( $friendId );
                        if($friend){
                            if( !isset($friend->username) ){
                                $friend->username   = "";
                            }
                            if( empty($friends[$friendId]) || $friend->is_deleted == 1 ){
                                $dislikedBy[]   = array( "user_id"=>"" , "name"=> "user", "profile_image"=>FORM_ACTION.DEFAULT_PROFILE_IMAGE );
                            }else{
                                $dislikedBy[]   = array( "user_id"=>$friendId , "name"=> $friend->username, "profile_image"=>FORM_ACTION.$friend->profile_image );
                            }
                        }
                    }
                    //sort details by username who liked/disliked
                    usort($likedBy, function($postA, $postB){
                        return strcmp($postA["name"], $postB["name"]);
                    });       
                    usort($dislikedBy, function($postA, $postB){
                        return strcmp($postA["name"], $postB["name"]);
                    });  
                    
                    $result = array("likes"=>$post->likes, "liked_by"=>$likedBy, "dislikes"=>$post->dislikes, "disliked_by"=>$dislikedBy);
                    Library::output(true, '1', "No Error", $result);
                }else{
                    Library::logging('error',"API : postLikeDislikeDetails : Invalid Post Id : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : dislikePost : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }

    function deletePost( $postId ) {
        $db     = Library::getMongo();
        $res    = $db->execute('return db.posts.remove({"_id" : ObjectId("'.$postId.'")})');
        if( empty($res['retval']["nRemoved"]) ) {
            Library::logging('error',"API : deletePost, mongodb error: ".$res['errmsg']." : post_id : ".$postId);
            Library::output(false, '0', POST_NOT_DELETED, null);
        }

    }
    
    function deleteSharedPost( $imageName ){
        $db     = Library::getMongo();
        $res    = $db->execute('return db.posts.remove( {text:"'.$imageName.'", type:3} )');
        if( $res["ok"] == 0 ) {
            Library::logging('error',"API : deletePost, mongodb error: ".$res['errmsg']." : shared_image : ".$imageName);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method to delete a post
     * @param $header_data: user and device details
     * @param $post_data: post request data containing:
     * - post_id: which is being liked
     * @author Saurabh Kumar
     * @return json
     */
    
    public function deletePostAction( $header_data, $post_data ){
        if(!isset($post_data['post_id'])) {
            Library::logging('alert',"API : deletePost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    $db     = Library::getMongo();
                    if( $post->user_id !== $header_data['id'] ){
                        Library::logging('error',"API : deletePost : ".POST_DELETE_AUTH_ERR." : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                        Library::output(false, '0', POST_DELETE_AUTH_ERR, null);
                    }
                    if($post->type == 3 ){
                        $res    = $db->execute('return db.posts.find( {text:"'.$post->text.'" } ).toArray()');
                        if( $res["ok"] == 0 ) {
                            Library::logging('error',"API : deletePost, mongodb error: ".$res['errmsg']." : user_id : ".$header_data['id']);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        }
                        if( count($res["retval"]) == 1 ){
                            require 'components/S3.php';
                            $s3         = new S3(AUTHKEY, SECRETKEY);
                            $bucketName = S3BUCKET;
                            if ( ! $s3->deleteObject($bucketName, $post->text) ) {
                                Library::logging('error',"API : deletePost : POST's FILE NOT DELETED FROM S3 Server : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                                Library::output(false, '0', POST_NOT_DELETED, null);
                            }
                        }
                    }
                    if($post->type == 2 ){
                        require 'components/S3.php';
                        $s3         = new S3(AUTHKEY, SECRETKEY);
                        $bucketName = S3BUCKET;
                        if(is_array($post->text)){
                            foreach( $post->text AS $pid){
                                $post   = $db->execute('return db.posts.find( { "_id" : ObjectId("'.$pid.'") }, {text:1} ).toArray()');
                                if($post['ok'] == 0) {
                                    Library::logging('error',"API : getImages (get user info) , mongodb error: ".$post['errmsg']." ".": user_id : ".$header_data['id']);
                                    Library::output(false, '0', ERROR_REQUEST, null);
                                }    
                                foreach( $post['retval'] As $postDetail ){
                                    if ( ! $s3->deleteObject($bucketName, $postDetail["text"]) ) {
                                        Library::logging('error',"API : deletePost : POST's FILE NOT DELETED FROM S3 Server : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                                        Library::output(false, '0', POST_NOT_DELETED, null);
                                    }
                                    $this->deletePost( (string)$postDetail['_id'] );
                                    
                                    // if this image was shared then delete shared posts 
                                    $this->deleteSharedPost( $postDetail["text"] );
                                }
                            }
                        }else{
                            if ( ! $s3->deleteObject($bucketName, $post->text) ) {
                                Library::logging('error',"API : deletePost : POST's FILE NOT DELETED FROM S3 Server : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                                Library::output(false, '0', POST_NOT_DELETED, null);
                            }
                            // if this image was shared then delete shared posts 
                            $this->deleteSharedPost( $post->text );
                        }
                    }
                    $this->deletePost($post_data['post_id']);
                    Library::output(true, '0', POST_DELETED, null);
                }else{
                    Library::logging('error',"API : deletePost : Invalid Post Id : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : deletePost : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
        
    }
    
    /**
     * Method to dislike a post
     * @param $header_data: user and device details
     * @param $post_data: post request data containing:
     * - post_id: which is being liked
     * @author Saurabh Kumar
     * @return json
     */
    
    public function getPostDetailsAction( $header_data, $post_data ){
        if(!isset($post_data['post_id'])) {
            Library::logging('alert',"API : getPostDetails : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    $result = $this->getPostDetail( $header_data["id"], $post_data['post_id'] );
                    Library::output(true, '1', "No Error", $result);
                }else{
                    Library::logging('error',"API : getPostDetails : Invalid Post Id : user_id : ".$header_data["id"].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : getPostDetails : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method to remove like from a post
     * @param $header_data: user and device details
     * @param $post_data: post request data containing:
     * - post_id: which is being liked
     * @author Saurabh Kumar
     * @return json
     */
    
    public function removeLikePostAction( $header_data, $post_data ){
        if(!isset($post_data['post_id'])) {
            Library::logging('alert',"API : removelikePost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    if( ! in_array( $header_data['id'], $post->liked_by) ){
                        Library::logging('error',"API : removelikePost : ".POST_NOT_LIKED." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', POST_NOT_LIKED, null);
                    }
                    $post->likes    -= 1;
                    foreach($post->liked_by AS $key=>$likedBy ){
                        if( $likedBy == $header_data['id'] ){
                            unset( $post->liked_by[$key] );
                        }
                    }
                    if($post->save()){
                        Library::output(true, '1', POST_LIKE_REMOVED, null);
                    }else{
                        foreach ($post->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : removelikePost : ".$errors." user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    }
                }else{
                    Library::logging('error',"API : removelikePost : Invalid Post Id : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : removelikePost : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method to remoive dislike from a post
     * @param $header_data: user and device details
     * @param $post_data: post request data containing:
     * - post_id: which is being liked
     * @author Saurabh Kumar
     * @return json
     */
    
    public function removeDislikePostAction( $header_data, $post_data ){
        if(!isset($post_data['post_id'])) {
            Library::logging('alert',"API : removeDislikePost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    if( ! in_array( $header_data['id'], $post->disliked_by) ){
                        Library::logging('error',"API : removeDislikePost : ".POST_NOT_DISLIKED." : user_id : ".$header_data['id']);
                        Library::output(false, '0', POST_NOT_DISLIKED, null);
                    }
                    $post->dislikes    -= 1;
                    foreach($post->disliked_by AS $key=>$dislikedBy ){
                        if( $dislikedBy == $header_data['id'] ){
                            unset( $post->disliked_by[$key] );
                        }
                    }
                    if($post->save()){
                        Library::output(true, '1', POST_DISLIKE_REMOVED, null);
                    }else{
                        foreach ($post->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : removeDislikePost : ".$errors." user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    }
                }else{
                    Library::logging('error',"API : removeDislikePost : Invalid Post Id : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch (Exception $ex) {
                Library::logging('error',"API : removeDislikePost : ".$ex." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
}
?>