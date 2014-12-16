<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\File as FileAdapter;

class UsersController 
{   

    /**
     * Method for new user registration
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function registrationAction($data)
    {   
        try {
            if(!isset($data['mobile_no']) || !isset($data['device_id'])) {
                Library::logging('alert',"API : registration : ".ERROR_INPUT);
                Library::output(false, '0', ERROR_INPUT, null);
            } else {
                $result = array();
                $mobile_no = $data['mobile_no'];
                $device_id = $data['device_id'];
                $record = Users::find(array(array("mobile_no"=>$mobile_no)));
                if(count($record) > 0) {
                    $result['user_id'] = $record[0]->_id;
                    $result['otp'] = 1234;
                    Library::output(true, '1', OTP_SENT, $result);
                } else {
                    $user  = new Users();
                    $user->mobile_no = $mobile_no;
                    $user->otp = 1234;
                    $user->device_id = $device_id;
                    $user->date = time();
                    $user->is_active = 0;
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : registration : ".$errors." ".$mobile_no);
                        Library::output(false, '0', $errors, null);
                    } else {
                        $result['user_id'] = $user->_id;
                        $result['otp'] = 1234;
                        Library::output(true, '1', OTP_SENT, $result);
                    }
                }
            }
        } catch (Exception $e) {
            Library::logging('error',"API : registration : ".$e." ".$mobile_no);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for generate token
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function generateTokenAction($header_data,$data)
    {    
        if(!isset($data['device_id'])) {
            Library::logging('alert',"API : generateToken : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
             try {
                $user_id = $header_data['id'];
                $device_id = $data['device_id'];
                $security = new \Phalcon\Security();
                $user = Users::findById($user_id);
                if($user) {
                    $result = array();
                    // generate new hash
                    $hash = KEY.'-'.$device_id;
                    $hash = $security->hash($hash);   
                    $user->hash = $hash;
                    $user->save();

                    $result['token'] = $hash;

                    Library::output(true, '1', TOKEN_MSG, $result);
                } else {
                    Library::output(false, '0', USER_NOT_REGISTERED, null);
                }
             } catch (Exception $e) {
                Library::logging('error',"API : generateToken : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
       
    }
    
    
    /**
     * Method for code verification
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function codeVerificationAction($header_data,$data)
    {
       if( !isset($data['otp_no'])) {
            Library::logging('alert',"API : codeVerification : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user_id = $header_data['id'];
                $otp_no = $data['otp_no'];
                $user = Users::findById($user_id);
                if($user->otp == $otp_no) {
                    $user->is_active = 1;
                    $user->username = "user";
                    $user->context_indicator = "Available";
                    $user->save();
                    Library::output(true, '1', OTP_VERIFIED, null);
                } else {
                    Library::logging('alert',OTP_WRONG." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', OTP_WRONG, null);
                }
            } catch (Exception $e) {
                Library::logging('error',"API : codeVerification : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    
    /**
     * Method for send contacts
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function sendContactsAction($header_data,$post_data)
    {   
       if( !isset($post_data['contact_numbers'])) {
            Library::logging('alert',"API : sendContacts : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user = Users::findById($header_data['id']);
                $user->contact_numbers = $post_data['contact_numbers'];
                $user->save();
                Library::output(true, '1', CONTACTS_SAVED, null);
            } catch(Exception $e) {
                Library::logging('error',"API : sendContacts : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    
    /**
     * Method for set display name
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function setDisplayNameAction($header_data,$name)
    { 
        try {
            $user = Users::findById($header_data['id']);
            if(empty($name)) {
                $user->username = '';
            } else {
                $user->username = $name;
            }
            $user->save();
            Library::output(true, '1', USER_NAME_SAVED, null);
        } catch (Exception $e) {
            Library::logging('error',"API : setDisplayName : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for get profile
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getProfileAction($header_data)
    { 
        $user_id = $header_data['id'];
        try {
            $result = array();
            $profile = array();
            $my_mind = array();
            $about_me = array();
            $user = Users::findById($user_id);
            $posts = Posts::find(array(array('user_id' => $user_id)));
           
            $profile['mobile_no'] = $user->mobile_no;
            $profile['username'] = $user->username;
            $profile['context_indicator'] = $user->context_indicator;
            $profile['birthday'] = isset($user->birthday) ? $user->birthday : '';
            $profile['profile_pic'] = 'http://cgintelmob.cafegive.com/images/slide_banner.jpg';
            
            $i = 0;
            foreach($posts as $post) {
                $my_mind[$i]['post_id'] = (string)$post->_id;
                $my_mind[$i]['post_text'] = $post->text;
                $my_mind[$i]['post_timestamp'] = $post->date;
                $my_mind[$i]['post_like_count'] = 0;
                $my_mind[$i]['post_dislike_count'] = 0;
                $my_mind[$i]['post_comment_count'] = $post->total_comment;
                $i++;
            }
            
            
            $about_me['gender'] = isset($user->gender) ? $user->gender : '';
            $about_me['hobbies'] = isset($user->hobbies) ? $user->hobbies : '';
            $about_me['description'] = isset($user->about_me) ? $user->about_me : '';
            
            $result['profile'] = $profile;
            $result['my_mind'] = $my_mind;
            $result['about_me'] = $about_me;
            Library::output(true, '1', "No Error", $result);
            
        } catch (Exception $e) {
            Library::logging('error',"API : getProfile : ".$e." ".": user_id : ".$user_id);
            Library::output(false, '0', ERROR_REQUEST, null);
        }   
    }
    
    
    /**
     * Method for get listing of indicators
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getIndicatorsAction()
    { 
        $result = array();
        $indicator = Indicators :: find();
        $result['indicator'] = $indicator[0]->indicators;
        Library::output(true, '1', "No Error", $result);
    }
    
    
    /**
     * Method for set display name
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
     public function setProfileAction($header_data,$post_data)
     { 
        if( !isset($post_data['username']) || !isset($post_data['birthday']) || !isset($post_data['gender']) || !isset($post_data['hobbies']) || !isset($post_data['about_me'])) {
            Library::logging('alert',"API : setProfile : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user_id = $header_data['id'];
                $user = Users::findById($header_data['id']);
                $user->username = $post_data['username'];
                $user->birthday = $post_data['birthday'];
                $user->gender = $post_data['gender'];
                $user->hobbies = $post_data['hobbies'];
                $user->about_me = $post_data['about_me'];
                if ($user->save() == false) {
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : setProfile : ".$errors." : user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                } else {
                    Library::output(true, '1', USER_PROFILE, null);
                }
            } catch (Exception $e) {
                Library::logging('error',"API : setProfile : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }  
        }
     }
     
     
    /**
     * Method for set context indicator
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
     public function setContextIndicatorAction($header_data,$context)
     {
        try {
            $user = Users::findById($header_data['id']);
            $user->context_indicator = $context;
            if ($user->save() == false) {
                foreach ($user->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                Library::logging('error',"API : setContextIndicator : ".$errors." : user_id : ".$header_data['id']);
                Library::output(false, '0', $errors, null);
            } else {
                Library::output(true, '1', CONTEXT_INDICATOR, null);
            }
        } catch (Exception $e) {
            Library::logging('error',"API : setContextIndicator : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }     
           
     }
     
     
    /**
     * Method for get registered numbers
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
     public function getRegisteredNumbersAction($header_data)
     {
         try {
            $user = Users::findById($header_data['id']);
            print_r($user->contact_numbers[0]);die;
            foreach($user->contact_numbers as $contacts) {
              $numbers[] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $contacts);
              
            }
            //print_r($numbers);die;
        } catch (Exception $e) {
            Library::logging('error',"API : setContextIndicator : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
         
  
}
