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
                
                mail($email_id, "Contact Us", $post_data['message'],$headers);
                
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
     * Method for reset password (forgot pass)
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
     * Method for about sociabile
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
    
    
    
    /**
     * Method for set privacy settings
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function setPrivacySettingsAction($header_data,$post_data)
    {
        if( !isset($post_data['group_ids']) || !isset($post_data['type'])) {
            Library::logging('alert',"API : setPrivacySettings : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 1) {
                    $group_ids = json_decode($post_data['group_ids']);
                } else {
                    $group_ids = $post_data['group_ids'];
                }
                $user = Users::findById($header_data['id']);
                if($post_data['type'] == 1) {
                    $user->my_mind_groups = $group_ids;
                } elseif ($post_data['type'] == 2) {
                    $user->about_me_groups = $group_ids;
                } elseif ($post_data['type'] == 3) {
                    $user->my_pictures_groups = $group_ids;
                } else {
                    Library::output(false, '0', "Wrong Type", null);
                }
                if ($user->save() == false) {
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                        Library::logging('error',"API : setPrivacySettings, error_msg : ".$errors." : user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                } else {
                    Library::output(true, '1', PRIVACY_SETTINGS, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : setPrivacySettings, error_msg : ".$e." ".": user_id : ".$header_data['id']."type :".$type);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    
    /**
     * Method for get privacy settings
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getPrivacySettingsAction($header_data,$type)
    {
        try {
            $result = array();
            $user = Users::findById($header_data['id']);
            if($type == 1) {
                $result['my_mind'] = $user->my_mind_groups;
                Library::output(true, '1', "No Error", $result);
            } elseif ($type == 2) {
                $result['my_mind'] = $user->about_me_groups;
                Library::output(true, '1', "No Error", $result);
            } elseif($type == 3) {
               $result['my_mind'] = $user->my_pictures_groups;
                Library::output(true, '1', "No Error", $result);
            } else {
                Library::output(false, '0', "Wrong Type", null);
            }
        } catch(Exception $e) {
            Library::logging('error',"API : getPrivacySettings, error_msg : ".$e." ".": user_id : ".$header_data['id']."type :".$type);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    
    /**
     * Method for share photos
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function sharePhotosAction($header_data,$post_data)
    {
        if( !isset($post_data['group_id']) || !isset($post_data['image_name'])) {
            Library::logging('alert',"API : sharePhotos : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 2) {
                    $group_ids =  json_encode($post_data['group_id']);
                } else {
                    $group_ids =  $post_data['group_id'];
                }
                
                $db = Library::getMongo();
                $request_sent = $db->execute('db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$push : {share_image:{$each:[{image_name:"'.$post_data['image_name'].'",group_id:'.$group_ids.'}]}}})');
                if($request_sent['ok'] == 0) {
                    Library::logging('error',"API : sharePhotos,mongodb error: ".$request_sent['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', SHARE_IMAGE, null);
            } catch(Exception $e) {
                Library::logging('error',"API : sharePhotos, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    
    /**
     * Method for get other friends profile info
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getFriendsInfoAction($header_data,$user_id)
    {
        if(empty($user_id)) {
            Library::logging('alert',"API : getFriendsInfo : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $i=0;
                $user_post = array();
                $user = Users::findById($user_id);
                if($user->running_groups) {

                    // loop for finding particular groups 
                    foreach ($user->running_groups as $group) {
                        if($group['user_id'] == $header_data['id']) {
                            $groups = $group['group_id'];
                            break;
                        } 
                    }
                    
                    if(is_array($groups)) {
                        
                        // loop for checking whether user can see my mind section
                        foreach($groups as $friends_group) {
                            foreach($user->my_mind_groups as $my_minds_groups) {
                                if($friends_group == $my_minds_groups) {
                                    $my_mind = 1;
                                    break;
                                } else {
                                    $my_mind = 0;
                                }
                            }
                            if($my_mind == 1) {
                                break;
                            }
                        }
                        
                        // loop for checking whether user can see about me section
                        foreach($groups as $friends_group) {
                            foreach($user->about_me_groups as $about_me_groups) {
                                if($friends_group == $about_me_groups) {
                                    $about_me = 1;
                                    break;
                                } else {
                                    $about_me = 0;
                                }
                            }
                            if($about_me == 1) {
                                break;
                            }
                        }
                        
                        
                        // loop for checking whether user can see my pictures section
                        foreach($groups as $friends_group) {
                            foreach($user->my_pictures_groups as $my_pictures_groups) {
                                if($friends_group == $my_pictures_groups) {
                                    $my_pictures = 1;
                                    break;
                                } else {
                                    $my_pictures = 0;
                                }
                            }
                            if($my_pictures == 1) {
                                break;
                            }
                        }

                        if($my_mind == 1) {
                            $posts = Posts::find(array(array("user_id" => $user_id)));
                            if(is_array($posts)) {
                                foreach($posts as $post) {
                                    $user_post[$i]['post_id'] = (string)$post->_id;
                                    $user_post[$i]['post_text'] = $post->text;
                                    $user_post[$i]['post_comment_count'] = $post->total_comment;
                                    $user_post[$i]['post_like_count'] = 0;
                                    $user_post[$i]['post_dislike_count'] = 0;
                                    $user_post[$i]['post_timestamp'] = $post->date;
                                    $i++;
                                }
                            } else {
                                $user_post = array();
                            }
                        }
                        
                        if($about_me == 1) {
                            $about_me_info['gender'] = isset($user->gender) ? $user->gender : '';
                            $about_me_info['hobbies'] = isset($user->hobbies) ? $user->hobbies : '';
                            $about_me_info['description'] = isset($user->about_me) ? $user->about_me : '';
                        } else {
                            $about_me_info['gender'] = '';
                            $about_me_info['hobbies'] = '';
                            $about_me_info['description'] = '';
                        }

                        if($my_pictures == 1) {
                            $my_pictures_info = isset($user->upload_image) ? $user->upload_image : '';
                        }
                        
                        $profile['image_url'] = FORM_ACTION;
                        $profile['mobile_no'] = $user->mobile_no;
                        $profile['username'] = $user->username;
                        $profile['context_indicator'] = $user->context_indicator;
                        $profile['birthday'] = isset($user->birthday) ? $user->birthday : '';
                        $profile['profile_pic'] = isset($user->profile_image) ? FORM_ACTION.$user->profile_image : 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg';

                        $result['profile'] = $profile;
                        $result['my_mind'] = $user_post;
                        $result['about_me'] = isset($about_me_info) ? $about_me_info : '';
                        if(empty($my_pictures_info)) {
                            $pic = array();
                            $result['my_pictures'] = $pic;
                        } else {
                            $result['my_pictures'] = $my_pictures_info;
                        }

                        Library::output(true, '1', "No Error", $result);
                    } else {
                        Library::output(false, '0', "Wrong User Id", null);
                    }

                } else {
                    Library::output(false, '0', INVALID_ID, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : getFriegndsInfo, error_msg : ".$e." ".": user_id : ".$header_data['id']."type :".$type);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    /**
     * Method for get Images
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getImagesAction($header_data,$type)
    {
        try {
            $db = Library::getMongo();
            $user = $db->execute('return db.users.find({"_id":ObjectId("'.$header_data['id'].'")}).toArray()');
            if($user['ok'] == 0) {
                Library::logging('error',"API : getImages (get user info) , mongodb error: ".$user['errmsg']." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }    
            if($type == 1) { // type 1 for upload images

                 $upload_image = array();
                 $result['image_url'] = FORM_ACTION;
                 $result['upload_images'] = isset($user['retval'][0]['upload_image']) ? $user['retval'][0]['upload_image'] : $upload_image;
                 Library::output(true, '1', "No Error", $result);
             
                 
            } elseif($type == 2) { // type 2 for share images
                $i=0;
                $share_images = array();
                 if(isset($user['retval'][0]['share_image'])) {
                     foreach($user['retval'][0]['share_image'] as $images) {
                         $user_share_image[$i] = $images['image_name'];
                         $i++;
                     }
                 }
                 if(isset($user['retval'][0]['running_groups'])) {
                     foreach ($user['retval'][0]['running_groups'] as $groups) {
                         $friends_info = $db->execute('return db.users.find({"_id":ObjectId("'.$groups['user_id'].'")}).toArray()');
                         if($friends_info['ok'] == 0) {
                            Library::logging('error',"API : getImages (get friends info) , mongodb error: ".$user['errmsg']." ".": user_id : ".$header_data['id']);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        } 
                        if($friends_info['retval'][0]['share_image']) {
                            if($friends_info['retval'][0]['running_groups']) {
                                foreach($friends_info['retval'][0]['running_groups'] as $groups) {
                                    if($groups['user_id'] == $header_data['id']) {
                                        foreach($groups['group_id'] as $ids) {
                                            foreach($friends_info['retval'][0]['share_image'] as $share_image_groups) {
                                                foreach($share_image_groups['group_id'] as $share_image_ids) {
                                                    if($ids == $share_image_ids) {
                                                        array_push($share_images,$share_image_groups['image_name']);
                                                        //print_r($share_image_groups['image_name']);echo "sdf";die;
                                                    }
                                                    //print_r($ids. " ".$share_image_ids . "\n");
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                     }
                 } 
                 $unique_share_images = array_unique ($share_images);
                 $sh_images = array();
                 $shared_images = array_merge($user_share_image,$unique_share_images);
                 $result['image_url'] = FORM_ACTION;
                 $result['share_images'] = isset($shared_images) ? $shared_images : $sh_images;
                 Library::output(true, '1', "No Error", $result);
             } else {
                 Library::output(false, '0', WRONG_TYPE, null);
             }
          
        } catch(Exception $e) {
            Library::logging('error',"API : getImages, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
    
    
    /**
     * Method for user login
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function userLoginAction($header_data,$post_data)
    {
        if(!isset($post_data['password']) || !isset($post_data['unique_id'])) {
            Library::logging('alert',"API : userLogin : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                if ($security->checkHash($post_data['password'], $user->password) && $user->unique_id == $post_data['unique_id']) {
                    Library::output(true, '1', USER_LOGIN, null);
                } else {
                    Library::output(false, '0', "Wrong Password", null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : userLogin, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
}
?>
