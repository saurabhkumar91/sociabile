<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;

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
        $os = $this->request->get("os");
        $version = $this->request->get("version");
        $mobile_no = $this->request->get("mobile_no");
        $device_id = $this->request->get("device_id");
        
        if(!isset($os) || !isset($version) || !isset($mobile_no) || !isset($device_id)) {
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
        $os = $this->request->get("os");
        $version = $this->request->get("version");
        $device_id = $this->request->get("device_id");
        $user_id = $this->request->get("user_id");
        if(!isset($os) || !isset($version) || !isset($user_id) || !isset($device_id)) {
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
        if(!isset($os) || !isset($version)  || !isset($user_id) || !isset($otp_no)) {
            $this->outputAction(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user = Users::findById($user_id);
                if($user->otp == $otp_no) {
                    $this->outputAction(true, '1', OTP_VERIFIED, null);
                } else {
                    $this->outputAction(false, '0', OTP_WRONG, null);
                }
            } catch (Exception $e) {
                $this->outputAction(false, '0', ERROR_REQUEST, null);
            }
        }
    }

    

}
