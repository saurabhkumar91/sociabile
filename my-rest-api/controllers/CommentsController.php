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
                        $post->total_comment = $post->total_comment+1;
                        $post->save();
                        $result['username'] = $user->username;
                        $result['comment_id'] = (string)$comment->_id;
                        $result['comment_text'] = $post_data['comment'];
                        $result['post_id'] = $comment->post_id;
                        $result['comment_timestamp'] = $comment->date;
                        $result['profile_pic'] = 'http://cgintelmob.cafegive.com/images/slide_banner.jpg';
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
                $mongo = new MongoClient();
                $db = $mongo->Sociabile;
                $comments = $db->execute('var comments = [] ;
                db.comments.find({"post_id":"'.$post_id.'","user_id":"'.$header_data['id'].'"}).forEach(
                function (newComments) { 
                    newComments.user = db.users.findOne({"_id":ObjectId(newComments.user_id)},{username:1,});
                    comments.push(newComments);
                    }
                    ); 
                    return comments;
                ');
               
                if(count($comments['retval'])> 0) {
                    $i=0;
                    foreach ($comments['retval'] as $comment) {
                        //print_r($comment);die;
                        $listing[$i]['username'] = isset($comment['user']['username']) ? $comment['user']['username'] : '';
                        $listing[$i]['comment_id'] = (string)$comment['_id'];
                        $listing[$i]['comment_text'] = $comment['comment_text'];
                        $listing[$i]['comment_timestamp'] = $comment['date'];
                        $listing[$i]['profile_pic'] = 'http://cgintelmob.cafegive.com/images/slide_banner.jpg';
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
