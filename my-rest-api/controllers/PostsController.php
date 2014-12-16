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
                $result = array();
                $post = new Posts();
                $post->user_id = $header_data['id'];
                $post->text = $post_data['post'];
                $post->total_comment = 0;
                $post->date = time();
                if ($post->save() == false) {
                    foreach ($post->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : createPost : ".$errors." user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                } else {
                    $result['post_id'] = (string)$post->_id;
                    $result['post_text'] = $post->text;
                    $result['post_comment_count'] = $post->total_comment;
                    $result['post_like_count'] = 0;
                    $result['post_dislike_count'] = 0;
                    $result['post_timestamp'] = $post->date;
                    Library::output(true, '1', POST_SAVED, $result);
                }
            } catch (Exception $e) {
                Library::logging('error',"API : createPost : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
}
?>
