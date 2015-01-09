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

    public function createsignatureAction($header_data,$type)
    {
        if($type == 1 || $type == 2 || $type == 3) {
            $form = array(
                'acl'                       => ACL,
                'success_action_redirect'   => SUCCESS_ACTION_REDIRECT,
                'bucket'                    => S3BUCKET,
            );

            $redirect_url = $form['success_action_redirect'].'/'.$header_data['id'].'/'.$type;
            $h =  date('H');
            $i =  date('i');
            $s =  date('s')+TOKEN_EXP_DURATION;
            $y =  date('Y');
            $m =  date('m');
            $d =  date('d');
            $expiration = $y."-".$m."-".$d."T".$h.":".$i.":".$s."Z";
            //print_r($expiration);

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
    
    public function getStatusAction($id,$type)
    {
        try {
            $image_name = $_GET['key'];
            $user = Users::findById($id);
            
            switch ($type) {
                
                // for profile image uploading
                case 1 :
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
                    if(isset($user->upload_image)) {
                        $upload_images = $user->upload_image;
                    } else {
                        $upload_images = array();
                    }
                    
                    array_push($upload_images,$image_name);
                    $user->upload_image = $upload_images;
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : getStatus amazon controller : ".$errors." : user_id : ".$id);
                        Library::output(false, '0', $errors, null);
                    } else {
                        $result['image_name'] = $image_name;
                        $result['upload_image'] = FORM_ACTION.$image_name;
                        Library::output(true, '1', IMAGE_UPLOAD, $result);
                    }
                    break;
                    
                // for share image uploading
                case 3 :
                    $result['image_name'] = $image_name;
                    $result['share_image'] = FORM_ACTION.$image_name;
                    Library::output(true, '1', IMAGE_UPLOAD, $result);
                    
                default:
			Library::output(false, '0', WRONG_TYPE, null);
            }
            
        } catch(Exception $e) {
            Library::logging('error',"API : getStatus : ".$e." ".":user_id : ".$id);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
		
}
	
	