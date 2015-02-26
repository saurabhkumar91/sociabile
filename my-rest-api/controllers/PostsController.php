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
                $postCount  = 0;
                $db         = Library::getMongo();
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
                        $postDetail["text"] = ($postDetail["type"]==2) ? FORM_ACTION.$postDetail["text"] : $postDetail["text"];
                        
                        $result[$postCount]["post_id"]              = (string)$postDetail["_id"];
                        $result[$postCount]["user_id"]              = $friendId;
                        $result[$postCount]["user_name"]            = $friend["name"];
                        $result[$postCount]["user_profile_image"]   = FORM_ACTION.$friend["profile_image"];
                        $result[$postCount]["text"]                 = $postDetail["text"];
                        $result[$postCount]["date"]                 = $postDetail["date"];
                        $result[$postCount]["likes"]                = $postDetail["likes"];
                        $result[$postCount]["dislikes"]             = $postDetail["dislikes"];
                        $result[$postCount]["total_comments"]       = $postDetail["total_comments"];
                        $result[$postCount]["is_liked"]             = $isLiked;
                        $result[$postCount]["is_disliked"]          = $isDisliked;
                        $result[$postCount]["post_type"]            = $postDetail["type"]; // type| 1 for text posts, 2 for images
                        $postCount++;
                    }
                }
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
                            $likedBy[]  = array( "name"=> $friend->username, "profile_image"=>FORM_ACTION.$friend->profile_image );
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
                            $dislikedBy[]   = array( "name"=> $friend->username, "profile_image"=>FORM_ACTION.$friend->profile_image );
                        }
                    }
                    
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
}
?>