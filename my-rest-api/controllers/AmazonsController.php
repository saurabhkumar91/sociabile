<?php


class AmazonsController 
{
    /**
     * Method for  Amazon Upload Ploicy and signature
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */

    public function createsignatureAction($header_data,$type, $param='')
    {
        if($type == 1 || $type == 2 || $type == 3  || $type == 4 || $type == 5 || $type == 6 || $type == 7 || $type == 10) {
            $form = array(
                'acl'                       => ACL,
                'success_action_redirect'   => SUCCESS_ACTION_REDIRECT,
                'bucket'                    => S3BUCKET,
            );

            if( $type== 6 ){
                $redirect_url = $form['success_action_redirect'].'/'.$header_data['id'].'/'.$type.'/'.$param;
            }else{
                $redirect_url = $form['success_action_redirect'].'/'.$header_data['id'].'/'.$type;
            }
            $h =  date('H');
            $i =  date('i');
            $s =  date('s')+TOKEN_EXP_DURATION;
            $y =  date('Y');
            $m =  date('m');
            $d =  date('d');
            $expiration = $y."-".$m."-".$d."T".$h.":".$i.":".$s."Z";

            $form['policy'] = '{
            "expiration": "'.$expiration.'",
                "conditions": [
                    {
                        "acl": "'.$form['acl'].'"
                    },
                    {
                        "success_action_redirect": "'.$redirect_url.'"
                    },
                    {
                        "bucket": "'.$form['bucket'].'"
                    },
                    [
                        "starts-with",
                        "$key",
                        ""
                    ],
                    [	"starts-with",
                        "$Content-Type",
                        ""
                    ]

                ]
            }';


            $signature = base64_encode(hash_hmac("sha1",base64_encode(utf8_encode($form['policy'])),SECRETKEY,true));
            $amazonsign = array();
            $amazonsign['policy'] = base64_encode($form['policy']);
            $amazonsign['signature'] = $signature;
            $amazonsign['AWSAccessKeyId'] = AUTHKEY;
            $amazonsign['acl'] = ACL;
            $amazonsign['success_action_redirect'] = $redirect_url;
            $amazonsign['form_action'] = FORM_ACTION;
            $amazonsign['key'] = '${filename}';
            if( $type == 1 ){
                $amazonsign['key'] = 'profiles/${filename}';
            }
            if( $type == 2 ){
                $amazonsign['key'] = 'uploaded/${filename}';
            }
            if( $type == 3 ){
                $amazonsign['key'] = 'shared/${filename}';
            }
            if( $type == 4 ){
                $amazonsign['key'] = 'chat/${filename}';
            }
            if( $type == 5 ){
                $amazonsign['key'] = 'emoticons/${filename}';
            }
            if( $type == 6 ){
                $amazonsign['key'] = 'timeCapsules/${filename}';
            }
            if( $type == 10 ){
                return $amazonsign;
            }
            Library::output(true,'0',"No Error",$amazonsign);
        } else {
            Library::output(false, '0', "Wrong Type", null);
        }
    }
       
    
    /**
     * Method for  get profile pic path
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getStatusAction( $id, $type, $param='' )
    {
        try {
            $image_name = $_GET['key'];
            switch ($type) {
                
                // for profile image uploading
                case 1 :
                    $user = Users::findById($id);
                    $user->profile_image = $image_name;
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : getStatus amazon controller : ".$errors." : user_id : ".$id);
                        Library::output(false, '0', $errors, null);
                    } else {
                        $result['profile_image'] = FORM_ACTION.$image_name;
                        Library::output(true, '1', USER_PROFILE_IMAGE, $result);
                    }
                    break;
                
                // for  image uploading
                case 2 :
                    try {
                        $result                 = array();
                        $post                   = new Posts();
                        $post->user_id          = $id;
                        $post->text             = $image_name;
                        $post->total_comments   = 0;
                        $post->likes            = 0;
                        $post->dislikes         = 0;
                        $post->date             = time();
                        $post->type             = 2;    // type| 1 for text posts, 2 for images
                        if ($post->save() == false) {
                            foreach ($post->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            Library::logging('error',"API : createPost : ".$errors." user_id : ".$id);
                            Library::output(false, '0', $errors, null);
                        } else {
                            $result['upload_image']         = FORM_ACTION.$image_name;
                            $result['post_id']              = (string)$post->_id;
                            $result['post_text']            = $post->text;
                            $result['post_comment_count']   = 0;
                            $result['post_like_count']      = 0;
                            $result['post_dislike_count']   = 0;
                            $result['post_timestamp']       = $post->date;
                            Library::output(true, '1', POST_SAVED, $result);
                        }
                    } catch (Exception $e) {
                        Library::logging('error',"API : createPost : ".$e." ".": user_id : ".$id);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                    
//                    
//                    $user = Users::findById($id);
//                    if(isset($user->upload_image)) {
//                        $upload_images = $user->upload_image;
//                    } else {
//                        $upload_images = array();
//                    }
//                    
//                    array_push($upload_images,$image_name);
//                    $user->upload_image = $upload_images;
//                    if ($user->save() == false) {
//                        foreach ($user->getMessages() as $message) {
//                            $errors[] = $message->getMessage();
//                        }
//                        Library::logging('error',"API : getStatus amazon controller : ".$errors." : user_id : ".$id);
//                        Library::output(false, '0', $errors, null);
//                    } else {
//                        $result['image_name'] = $image_name;
//                        $result['upload_image'] = FORM_ACTION.$image_name;
//                        Library::output(true, '1', IMAGE_UPLOAD, $result);
//                    }
                    break;
                    
                // for share image uploading
                case 3 :
                    $result['image_name'] = $image_name;
                    $result['share_image'] = FORM_ACTION.$image_name;
                    Library::output(true, '1', IMAGE_UPLOAD, $result);
                    break;
                
                // for uploading chat image and creating thumbnail for it
                case 4 :
                    $amazonSign = $this->createsignatureAction( array("id"=>$id), 10 );
                    $url        = $amazonSign['form_action'];
                    $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                    $img        = explode("/", $image_name);
                    $imgName    = end($img);
                    $ext        = explode(".", $imgName);
                    $extension  = trim(end($ext));
                    if( !in_array($extension, array("jpeg", "png", "gif"))){
                        $extension  = "jpeg";
                    }
                    $postfields = array(
                        "key"                       =>  "thumbnail/".$imgName,//$amazonSign["key"],
                        "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                        "acl"                       => $amazonSign["acl"],
                        "success_action_redirect"   => $amazonSign["success_action_redirect"],
                        "policy"                    => $amazonSign["policy"],
                        "signature"                 => $amazonSign["signature"],
                        "Content-Type"              => "image/$extension",
                        "file"                      => $this->createThumbnail(FORM_ACTION.$image_name)
                    );
                    
                    $ch = curl_init();
                    $options = array(
                        CURLOPT_URL         => $url,
                        //CURLOPT_HEADER      => true,
                        CURLOPT_POST        => 1,
                        CURLOPT_HTTPHEADER  => $headers,
                        CURLOPT_POSTFIELDS  => $postfields,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true
                    ); // cURL options
                    curl_setopt_array($ch, $options);
                    $thumbnailName      = curl_exec($ch);
                    $result['image']    = FORM_ACTION.$image_name;
                    curl_close($ch);
                    if(is_string ( $thumbnailName )){
                        $result['thumbnail']    = FORM_ACTION.$thumbnailName;
                    }else{
                        Library::output(false, '0', "Thumbnail Not Created.", null);
                    }
                    Library::output(true, '1', IMAGE_UPLOAD, $result);
                    break;
                // for emoticons image uploading
                case 5 :
                    $result['emoticonImage'] = FORM_ACTION.$image_name;
                    Library::output(true, '1', IMAGE_UPLOAD, $result);
                    break;
                // for time capsule image
                case 6 :
                    $timeCapsules   = TimeCapsules::findById($param);
                    if( !$timeCapsules ){
                        Library::logging('error',"API : getStatus amazon controller : ".INVALID_CAPSULE." : user_id : ".$id);
                        Library::output(false, '0', INVALID_CAPSULE, null);
                    }
                    $timeCapsules->capsule_image[]  = $image_name;
                    if ( $timeCapsules->save() == false ) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : getStatus amazon controller : ".$errors." : user_id : ".$id);
                        Library::output(false, '0', $errors, null);
                    } else {
                        Library::output(true, '1', TIME_CAPSULE_IMAGE, null);
                    }
                    break;
                
                // for multiple images (as single post) uploading
                case 7 :
                    try {
                        $createdAt              = time();
                        $post                   = new Posts();
                        $post->user_id          = $id;
                        $post->text             = $image_name;
                        $post->total_comments   = 0;
                        $post->likes            = 0;
                        $post->dislikes         = 0;
                        $post->date             = $createdAt;
                        $post->type             = 2;    // type| 1 for text posts, 2 for images
                        if ($post->save() == false) {
                            foreach ($post->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            Library::logging('error',"API : getStatus : ".$errors." user_id : ".$id);
                            Library::output(false, '0', $errors, null);
                        }
                        $postId = (string)$post->_id;
                        $db     = Library::getMongo();
                        if( !empty($param) ){
                            $result = $db->execute('return db.posts.find( {"_id" : ObjectId("'.$param.'") } ).toArray()');
                            if( $result['ok'] == 0 ){
                                Library::logging('error',"API : getStatus, mongodb error: ".$result['errmsg']." : user_id : ".$id);
                                Library::output(false, '0', "Unable to get details from database", null);
                            }
                        }
                        if( !empty( $result["retval"][0] ) ){
                            $update = $db->execute('db.posts.update( {"_id" : ObjectId("'.$param.'")}, { $push: { text: "'.$postId.'" } } )');
                            if( $update['ok'] == 0 ){
                                Library::logging('error',"API : getStatus, mongodb error: ".$update['errmsg']." : user_id : ".$id);
                                Library::output(false, '0', "Unable to update images", null);
                            }
                        }else{
                            $update = $db->execute('db.posts.insert( { "user_id" : "'.$id.'", text: ["'.$postId.'"], total_comments:0, likes:0, dislikes:0, date: "'.$createdAt.'", type:2 } )');
                            if( $update['ok'] == 0 ){
                                Library::logging('error',"API : getStatus, mongodb error: ".$update['errmsg']." : user_id : ".$id);
                                Library::output(false, '0', "Unable to update images", null);
                            }
                            $result = $db->execute('return db.posts.find( { "user_id" : "'.$id.'", text: ["'.$postId.'"], date: "'.$createdAt.'", type:2 } ).toArray()');
                        }
                        $res['upload_image']         = FORM_ACTION.$image_name;
                        $res['post_id']              = (string)$result["retval"][0]["_id"];
                        $res['post_text']            = $result["retval"][0]["text"];
                        $res['post_comment_count']   = $result["retval"][0]["total_comments"];
                        $res['post_like_count']      = $result["retval"][0]["likes"];
                        $res['post_dislike_count']   = $result["retval"][0]["dislikes"];
                        $res['post_timestamp']       = $result["retval"][0]["date"];
                        Library::output(true, '1', POST_SAVED, $res);
                    } catch (Exception $e) {
                        Library::logging('error',"API : createPost : ".$e." ".": user_id : ".$id);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                    break;
                
                // for thumbnail uploading
                case 10 :
                    exit($image_name);
                default:
			Library::output(false, '0', WRONG_TYPE, null);
            }
            
        } catch(Exception $e) {
            Library::logging('error',"API : getStatus : ".$e." ".":user_id : ".$id);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }

    function createThumbnail($image_name){
        require("components/Image.php");
        $imageComponent = new Image();
        $quality    = 20;
        $img        = get_headers($image_name, 1);
        if( !empty($img["Content-Length"]) ){
            $quality    = ceil( 2048*100/($img["Content-Length"]) );
            $quality    = ($quality>100) ? 100 : $quality;
        }
        $thumbnail  = $imageComponent->resize($image_name, null, 100, 100, $quality);
        return $thumbnail;
    }
    
}
	