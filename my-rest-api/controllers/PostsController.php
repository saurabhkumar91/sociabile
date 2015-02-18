<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\File as FileAdapter;

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
    
    public function createPostAction($header_data,$post_data)
    { 
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
    
    public function getPostsAction($header_data,$post_data)
    { 
        if(!isset($post_data['groups'])) {
            Library::logging('alert',"API : createPost : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $friends    = array();
                $user = Users::findById($header_data['id']);
                $i=0;
                if( !is_array($post_data['groups']) ){
                    $post_data['groups']    = array($post_data['groups']);
                }
                if(isset($user->running_groups)) {
                    foreach($user->running_groups as $user_ids) {
                        if( count(array_intersect($user_ids['group_id'], $post_data['groups'])) > 0 ) {
                            
                            $friend         = Users::findById( $user_ids['user_id'] );
                            
                            if( !isset($friend->username) ){
                                $friend->username   = "";
                            }
                            if( !isset($friend->profile_image) ){
                                $friend->profile_image  = "";
                            }
                            $friends[$i]["id"]              = (string)$friend->_id;
                            $friends[$i]["name"]            = $friend->username;
                            $friends[$i]["profile_image"]   = $friend->profile_image;
                            $i++;
                        }
                    }
                }
                $result = array();
                $posts  = new Posts();
                $postCount  = 0;
                foreach( $friends AS $friend ){
                    $post   = $posts->find( array("conditions"=>array( "user_id"=>$friend["id"]))  );
                    foreach( $post As $postDetail ){
                        $comments   = array();
                        if( !empty($postDetail->comments) ){
                            $comments   = $postDetail->comments;
                        }
                        $result[$postCount]["post_id"]              = (string)$postDetail->_id;
                        $result[$postCount]["friend_id"]            = $friend["id"];
                        $result[$postCount]["friend_name"]          = $friend["name"];
                        $result[$postCount]["friend_profile_image"] = $friend["profile_image"];
                        $result[$postCount]["text"]                 = $postDetail->text;
                        $result[$postCount]["date"]                 = $postDetail->date;
                        $result[$postCount]["likes"]                = $postDetail->likes;
                        $result[$postCount]["dislikes"]             = $postDetail->dislikes;
                        $result[$postCount]["total_comments"]       = $postDetail->total_comments;
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
}
?>