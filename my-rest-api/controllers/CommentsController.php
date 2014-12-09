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
            Library::logging('alert',"API : postComments : ".ERROR_INPUT.": user_id : ".$header_data['user_id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $result = array();
                $post = Posts::findById($post_data['post_id']);
                if($post->_id) {
                    $comment = new Comments();
                    $comment->post_id = $post_data['post_id'];
                    $comment->user_id = $header_data['user_id'];
                    $comment->comment_text = $post_data['comment'];
                    $comment->date = time();
                    if ($comment->save() == false) {
                        foreach ($comment->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : postComments : ".$errors." user_id : ".$header_data['user_id']);
                        Library::output(false, '0', $errors, null);
                    } else {
                        $post->total_comment = $post->total_comment+1;
                        $post->save();
                        $result['comment_id'] = $comment->_id;
                        $result['post_id'] = $comment->post_id;
                        Library::output(true, '1', COMMENT_SAVED, $result);
                    }
                }
               
            } catch (Exception $e) {
                Library::logging('error',"API : postComments : ".$e." ".": user_id : ".$header_data['user_id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
}
?>
