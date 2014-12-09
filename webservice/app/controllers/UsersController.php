<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\File as FileAdapter;

class UsersController extends ControllerBase
{   
//     public function beforeExecuteRoute($dispatcher)
//    {
//        // Executed before every found action
//         print_r($dispatcher->getActionName());die;
//    }

    /**
     * Method for new user registration
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function registrationAction()
    {
        $os = $this->request->getPost("os");
        $version = $this->request->getPost("version");
        $mobile_no = $this->request->getPost("mobile_no");
        $device_id = $this->request->getPost("device_id");
        
        if(!isset($os) || !isset($version) || !isset($mobile_no) || !isset($device_id)) {
            $this->loggingAction('alert',"API : registration : ".ERROR_INPUT);
            $this->outputAction(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $result = array();
                $record = Users::find(array(array("mobile_no"=>$mobile_no)));
                if(count($record) > 0) {
                    $result['user_id'] = $record[0]->_id;
                    $result['otp'] = 123456;
                    $this->outputAction(true, '1', OTP_SENT, $result);
                } else {
                    $user  = new Users();
                    $user->mobile_no = $mobile_no;
                    $user->otp = 123456;
                    $user->device_id = $device_id;
                    $user->date = time();
                    $user->is_active = 0;
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            echo $message, "\n";
                        }
                    } else {
                        $result['user_id'] = $user->_id;
                        $result['otp'] = 123456;
                        $this->outputAction(true, '1', OTP_SENT, $result);
                    }
                }
               
            } catch (Exception $e) {
                $this->loggingAction('error',"API : registration : ".$e." ".$mobile_no);
                $this->outputAction(false, '0', ERROR_REQUEST, null);
            }
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
    
    public function generateTokenAction()
    {
        $os = $this->request->getPost("os");
        $version = $this->request->getPost("version");
        $device_id = $this->request->getPost("device_id");
        $user_id = $this->request->getPost("user_id");
        if(!isset($os) || !isset($version) || !isset($user_id) || !isset($device_id)) {
            $this->loggingAction('alert',"API : generateToken : ".ERROR_INPUT.": user_id : ".$user_id);
            $this->outputAction(false, '0', ERROR_INPUT, null);
        } else {
            try {
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

                    $this->outputAction(true, '1', TOKEN_MSG, $result);
                } else {
                    $this->outputAction(false, '0', USER_NOT_REGISTERED, null);
                }
             } catch (Exception $e) {
                $this->loggingAction('error',"API : generateToken : ".$e." ".": user_id : ".$user_id);
                $this->outputAction(false, '0', ERROR_REQUEST, null);
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
    
    public function codeVerificationAction()
    {
        $os = $this->request->getPost("os");
    	$version = $this->request->getPost("version");
        $user_id = $this->request->getPost("user_id");
        $otp_no = $this->request->getPost("otp_no");
        if( !isset($otp_no)) {
            $this->loggingAction('alert',"API : codeVerification : ".ERROR_INPUT.": user_id : ".$user_id);
            $this->outputAction(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user = Users::findById($user_id);
                if($user->otp == $otp_no) {
                    $user->is_active = 1;
                    $user->username = "user";
                    $user->context_indicator = "Available";
                    $user->save();
                    $this->outputAction(true, '1', OTP_VERIFIED, null);
                } else {
                    $this->loggingAction('alert',OTP_WRONG." ".$user_id);
                    $this->outputAction(false, '0', OTP_WRONG, null);
                }
            } catch (Exception $e) {
                $this->loggingAction('error',"API : codeVerification : ".$e." ".": user_id : ".$user_id);
                $this->outputAction(false, '0', ERROR_REQUEST, null);
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
    
    public function sendContactsAction()
    {   
        $user_id = $this->request->getPost("user_id");
        $contact_numbers = $this->request->getPost("contact_numbers");
        if( !isset($contact_numbers)) {
            $this->loggingAction('alert',"API : sendContacts : ".ERROR_INPUT.": user_id : ".$user_id);
            $this->outputAction(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user = Users::findById($user_id);
                $user->contact_numbers = $contact_numbers;
                $user->save();
                $this->outputAction(true, '1', CONTACTS_SAVED, null);
            } catch(Exception $e) {
                $this->loggingAction('error',"API : sendContacts : ".$e." ".": user_id : ".$user_id);
                $this->outputAction(false, '0', ERROR_REQUEST, null);
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
    
    public function setDisplayNameAction()
    { 
        $user_id = $this->request->get("user_id");
        $username = $this->request->get("user_name");
        if( !isset($username)) {
            $this->loggingAction('alert',"API : setDisplayName : ".ERROR_INPUT.": user_id : ".$user_id);
            $this->outputAction(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user = Users::findById($user_id);
                $user->username = $username;
                $user->save();
                $this->outputAction(true, '1', USER_NAME_SAVED, null);
            } catch (Exception $e) {
                $this->loggingAction('error',"API : setDisplayName : ".$e." ".": user_id : ".$user_id);
                $this->outputAction(false, '0', ERROR_REQUEST, null);
            }
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
    
    public function getProfileAction()
    { 
        $user_id = $this->request->get("user_id");
        try {
            $result = array();
            $profile = array();
            $my_mind = array();
            $about_me = array();
            $user = Users::findById($user_id);
            
            $profile['mobile_no'] = $user->mobile_no;
            $profile['username'] = $user->username;
            $profile['context_indicator'] = $user->context_indicator;
            $profile['birthday'] = '10-04-2014';
            $profile['profile_pic'] = 'http://cgintelmob.cafegive.com/images/slide_banner.jpg';
            
            $my_mind[0]['post_id'] = 1;
            $my_mind[0]['post_text'] = 'watsapp';
            $my_mind[0]['post_timestamp'] = 1417587673;
            $my_mind[0]['post_like_count'] = 1;
            $my_mind[0]['post_dislike_count'] = 1;
            $my_mind[0]['post_comment_count'] = 1;
            
            $about_me['gender'] = 'male';
            $about_me['hobbies'] = 'cricket';
            $about_me['description'] = 'hello';
            
            $result['profile'] = $profile;
            $result['my_mind'] = $my_mind;
            $result['about_me'] = $about_me;
            $this->outputAction(true, '1', "No Error", $result);
            
        } catch (Exception $e) {
            $this->loggingAction('error',"API : getProfile : ".$e." ".": user_id : ".$user_id);
            $this->outputAction(false, '0', ERROR_REQUEST, null);
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
        $this->outputAction(true, '1', "No Error", $result);
        print_r($indicator[0]->indicators);die;
    }
        

    

}
