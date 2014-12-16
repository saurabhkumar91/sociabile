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

    public function createsignatureAction($header_data)
    {
        $form = array(
            'acl'                       => ACL,
            'success_action_redirect'   => SUCCESS_ACTION_REDIRECT,
            'bucket'                    => S3BUCKET,
        );

        $redirect_url = $form['success_action_redirect'].'/'.$header_data['user_id'];
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
    
    public function getStatusAction($user_id)
    {
        try {
            $image_name = $_GET['key'];
            $user = Users::findById($user_id);
            $user->profile_image = $image_name;
            $user->save();
            $result['profile_image'] = FORM_ACTION.$image_name;
            Library::output(true, '1', USER_PROFILE_IMAGE, $result);
            
        } catch(Exception $e) {
            Library::logging('error',"API : getStatus : ".$e." ".": user_id : ".$user_id);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
		
}
	
	