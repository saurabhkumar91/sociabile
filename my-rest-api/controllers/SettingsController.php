<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class SettingsController 
{ 
    
    /**
     * Method for change phone number change
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function generateOTPAction($header_data,$post_data)
    { 
        try {
            if( !isset($post_data['type'])) {
                Library::logging('alert',"API : generateOTP : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_INPUT, null);
            } else {
                $user = Users::findById($header_data['id']);
                if($post_data['type'] == 1) { // for change mobile no
                    if( !isset($post_data['mobile_no'])) {
                        Library::logging('alert',"API : generateOTP : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_INPUT, null);
                    } else {
                        if($user) {
                            $user->change_mobile_no = $post_data['mobile_no'];
                            $user->otp = 1234;

                            if ($user->save() == false) {
                                foreach ($user->getMessages() as $message) {
                                    $errors[] = $message->getMessage();
                                }
                                Library::logging('error',"API : generateOTP, error_msg : ".$errors." : user_id : ".$header_data['id']);
                                Library::output(false, '0', $errors, null);
                            } else {
                                $result['otp'] = 1234;
                                Library::output(true, '1', "OTP Sent Successfully", $result);
                            }
                        } else {
                             Library::output(false, '0', USER_NOT_REGISTERED, null);
                        }
                    }
                } elseif ($post_data['type'] == 2) { // for change password
                     if( !isset($post_data['email_id'])) {
                        Library::logging('alert',"API : generateOTP : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_INPUT, null);
                    } else {
                        if($user) {
                            $emails = array();
                            array_push($emails,$post_data['email_id']);
                            $user->otp = 1234;
                            $user->email_id = $emails;

                            if ($user->save() == false) {
                                foreach ($user->getMessages() as $message) {
                                    $errors[] = $message->getMessage();
                                }
                                Library::logging('error',"API : generateOTP, error_msg : ".$errors." : user_id : ".$header_data['id']);
                                Library::output(false, '0', $errors, null);
                            } else {
                                ini_set("SMTP", "smtp.gmail.com");            
                                ini_set("smtp_port", 465);
                                ini_set("auth_username", "shubham150@gmail.com");
                                ini_set("sendmail_from", "shubham150@gmail.com");
                                ini_set("auth_password", "");

                                $headers = "MIME-Version: 1.0" . "\r\n";
                                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                                //$headers .= 'From: <support@cafegive.com>' . "\r\n";

                                $message = "Your OTP is : 1234";

                                mail($post_data['email_id'], "Forgot Password | Sociabile", $message,$headers);
                                Library::output(true, '1', "OTP Sent Successfully", null);
                            }
                        } else {
                             Library::output(false, '0', USER_NOT_REGISTERED, null);
                        }
                    }
                } else {
                    Library::logging('error',"API : changeNumber :, error_msg : wrong type, user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            }
            
        } catch(Exception $e) {
            Library::logging('error',"API : changeNumber : ".$e." user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    /**
     * Method for change phone number change
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function changeNumberAction($header_data,$post_data)
    {   
        if( !isset($post_data['otp_no'])) {
            Library::logging('alert',"API : changeNumber : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
             try {
                 
                 $user = Users::findById($header_data['id']);
                 if($user->otp == $post_data['otp_no']) {
                    $old_mobile_no = $user->mobile_no;
                    $new_mobile_no = $user->change_mobile_no;
                    $user->mobile_no = $new_mobile_no;
                    $user->change_mobile_no = $old_mobile_no;
                    if ($user->save() == false) {
                       foreach ($user->getMessages() as $message) {
                           $errors[] = $message->getMessage();
                       }
                       Library::logging('error',"API : changeNumber : ".$errors." : user_id : ".$header_data['id']);
                       Library::output(false, '0', $errors, null);
                   } else {
                       Library::output(true, '1', CHANGE_NUMBER, null);
                   }
                 } else {
                     Library::logging('alert',OTP_WRONG." ".": user_id : ".$header_data['id']);
                     Library::output(false, '0', OTP_WRONG, null);
                 }
                 
            } catch(Exception $e) {
                Library::logging('error',"API : changeNumber, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method for change phone number change
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function aboutChatAction($header_data)
    {  
        try {
            $ques = array("How do a start a new chat?",
                            "How do I create a group chat?",
                            "How do I purchase emoticons?",
                            "How do I send emoticons?",
                            "Can I use normal emoticons?");
            $ans = array("Download the app",
                            "Create the group",
                            "Click on pay button",
                            "Click on pay button",
                            "yes"
                            );
            $result['ques'] = $ques;
            $result['ans'] = $ans;
            
            Library::output(true, '1', "No Error", $result);
        } catch(Exception $e) {
            Library::logging('error',"API : aboutChat, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for get Category
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getCategoryAction($header_data)
    {
        try {
                $category = Category::find();
                $i = 0;
                $result = array();
                foreach($category as $cat) {
                    $result[$i]['id'] = (string)$cat->_id;
                    $result[$i]['name'] = (string)$cat->name;
                    $i++;
                }
                Library::output(true, '1', "No Error", $result);
            } catch(Exception $e) {
                Library::logging('error',"API : getCategory, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
        }
    
    }
    
    /**
     * Method for contact us API
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function contactUsAction($header_data,$post_data)
    {
        if( !isset($post_data['cat_id']) || !isset($post_data['message']) || !isset($post_data['email_id']) || !isset($post_data['user_device']) || !isset($post_data['device_model'])) {
            Library::logging('alert',"API : contactUs : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                 $request = 'db.contact_us.insert({ 
                            user_id : "'.$header_data['id'].'",
                            cat_id: "'.$post_data['cat_id'].'", 
                            message: "'.$post_data['message'].'",
                            email_id: "'.$post_data['email_id'].'",
                            user_device: "'.$post_data['user_device'].'",
                            device_model: "'.$post_data['device_model'].'",
                            user_agent: "'.$_SERVER['HTTP_USER_AGENT'].'"
                    })';
                 
                $db = Library::getMongo();
                $result =  $db->execute($request);
                if($result['ok'] == 0) {
                    Library::logging('error',"API : contactUs, error_msg: ".$result['errmsg']." ".": user_id : ".$header_data['id']);
                }

                ini_set("SMTP", "smtp.gmail.com");            
                ini_set("smtp_port", 465);
                ini_set("auth_username", "shubham150@gmail.com");
                ini_set("sendmail_from", "shubham150@gmail.com");
                ini_set("auth_password", "");

            
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                //$headers .= 'From: <support@cafegive.com>' . "\r\n";
                                
                $email_id = "shubham150@gmail.com";
                
                var_dump(mail($email_id, "Contact Us", $post_data['message'],$headers));
                
                Library::output(true, '1', "Post Sent Successfully.",null);
            } catch(Exception $e) {
                Library::logging('error',"API : contactUs, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method for set password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function setPasswordAction($header_data,$post_data)
    {
        if( !isset($post_data['email_id']) || !isset($post_data['password'])) {
            Library::logging('alert',"API : setPassword : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $emails = array();
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                 array_push($emails,$post_data['email_id']);
                $user->email_id = $emails;
                $user->password = $security->hash($post_data['password']);
                if ($user->save() == false) {
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : setPassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                } else {
                    Library::output(true, '1', SET_PASSWORD, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : setPassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    
    /**
     * Method for reset password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function resetPasswordAction($header_data,$post_data)
    {
        if( !isset($post_data['otp_no']) || !isset($post_data['password'])) {
            Library::logging('alert',"API : resetPassword : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                if($user) {
                    if($user->otp == $post_data['otp_no']) {
                        $user->password = $security->hash($post_data['password']);

                        if ($user->save() == false) {
                            foreach ($user->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            Library::logging('error',"API : resetPassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                            Library::output(false, '0', $errors, null);
                        } else {
                           Library::output(true, '1', SET_PASSWORD, null);
                        }
                    } else {
                        Library::logging('alert',OTP_WRONG." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', OTP_WRONG, null);
                    }
                } else {
                     Library::output(false, '0', USER_NOT_REGISTERED, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : resetPassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
        
    }
    
    
    /**
     * Method for delete password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function deletePasswordAction($header_data)
    {
        try {
            $user = Users::findById($header_data['id']);
            $user->password = '';
            if ($user->save() == false) {
                foreach ($user->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                Library::logging('error',"API : deletePassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                Library::output(false, '0', $errors, null);
            } else {
                Library::output(true, '1', DELETE_PASSWORD, null);
            }
        } catch(Exception $e) {
            Library::logging('error',"API : deletePassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for change password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function changePasswordAction($header_data,$post_data)
    {
        if( !isset($post_data['old_password']) || !isset($post_data['new_password'])) {
            Library::logging('alert',"API : changePassword : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                if ($security->checkHash($post_data['old_password'], $user->password)) {
                    $new_password = $security->hash($post_data['new_password']);
                    $user->password = $new_password;
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                            Library::logging('error',"API : changePassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                            Library::output(false, '0', $errors, null);
                    } else {
                        Library::output(true, '1', SET_PASSWORD, null);
                    }

                } else {
                    Library::output(false, '0', "Wrong Password", null);
                }
            } catch (Exception $e) {
                Library::logging('error',"API : changePassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    
    /**
     * Method for change password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function aboutSoicabileAction($header_data,$type)
    {
        try {
            if($type == 1) {
                $message['terms'] = "These Terms of Service apply to all users of the WhatsApp Service. Information provided by our users through the WhatsApp Service may contain links to third party websites that are not owned or controlled by WhatsApp. WhatsApp has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party websites. ";
                Library::output(true, '1', "No Error", $message);
            } elseif ($type == 2) {
                $message['privacy'] = "In order to access and use the features of the Service, you acknowledge and agree that you will have to provide WhatsApp with your mobile phone number";
                Library::output(true, '1', "No Error", $message);
            } else {
                Library::output(false, '0', "Wrong Type", null);
            }
        } catch(Exception $e) {
            Library::logging('error',"API : aboutSoicabile, error_msg : ".$e." ".": user_id : ".$header_data['id']."type :".$type);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
}
?>
