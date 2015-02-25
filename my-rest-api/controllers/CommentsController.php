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
                        $result['username'] = $user->username;
                        $result['comment_id'] = (string)$comment->_id;
                        $result['comment_text'] = $post_data['comment'];
                        $result['post_id'] = $comment->post_id;
                        $result['comment_timestamp'] = $comment->date;
                        $result['profile_pic'] = isset($user->profile_image) ? FORM_ACTION.$user->profile_image : 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg';
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
                $db = Library::getMongo();
                $comments = $db->execute('var comments = [] ;
                db.comments.find({"post_id":"'.$post_id.'"}).forEach(
                function (newComments) { 
                    newComments.user = db.users.findOne({"_id":ObjectId(newComments.user_id)},{username:1,profile_image:1});
                    comments.push(newComments);
                    }
                    ); 
                    return comments;
                ');
               
                if(count($comments['retval'])> 0) {
                    $i=0;
                    foreach ($comments['retval'] as $comment) {
                        $listing[$i]['username'] = isset($comment['user']['username']) ? $comment['user']['username'] : '';
                        $listing[$i]['comment_id'] = (string)$comment['_id'];
                        $listing[$i]['comment_text'] = $comment['comment_text'];
                        $listing[$i]['comment_timestamp'] = $comment['date'];
                        $listing[$i]['profile_pic'] = $comment['user']['profile_image'];
                        $i++;
                    }
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
    
  
}
?>
