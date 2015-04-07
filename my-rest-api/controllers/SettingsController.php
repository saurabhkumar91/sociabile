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
                            $mobileNo   = Users::find(array( array("mobile_no"=>$post_data['mobile_no']) ));
                            if( !empty($mobileNo[0]) && ($mobileNo[0]->is_deleted == 0) ){
                                Library::logging('alert',"API : generateOTP : mobile no exists : user_id : ".$header_data['id']);
                                Library::output(false, '0', "This mobile no already exists.", null);
                            }
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
                                $message = "Your OTP is : 1234";
                                Library::sendMail( $post_data['email_id'], $message, "Forgot Password | Sociabile" );
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
                                
                $email_id = "shubham150@gmail.com";
                
                Library::sendMail( $email_id, $post_data['message'], "Contact Us" );
                
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
                    Library::output(false, '0', INVALID_LOGIN, null);
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
    
    public function aboutSociabileAction($header_data,$type)
    {
        try {
            if($type == 1) {
                $message['terms'] = "<h3>Sociabile Terms of Service</h3><p><u>Welcome to Sociabile</u></p><p><i>Introduction to Sociabile</i></p><p>Thank you for choosing to use Sociabile. Sociabile is a portmanteau combining the words “Social” and “Mobile” to provide a social networking service based in a mobile environment. Sociabile is committed to providing a private social networking platform allowing you to control with whom you share information and includes many features that are designed to control who views your information. To view more information on how Sociabile takes your privacy seriously and helps you protect your information, view our Privacy Policy.</p><p><i>Introduction of Services</i></p><p>Sociabile provides a social networking platform where you may share content with other users who have been connected through the application. You may provide information on your profile, load images, or add My Mind messages to let others know what you are thinking. Only connected users with permission can view this information. You may comment, like, or dislike images or My Mind messages posted by connections made if proper permissions are granted. You may also share images with connections made. Any images shared will appear in the shared images tab under My Photos. A connected user can only view the My Shared photos that have been shared with him or her. To grant permission to connected users, upon establishing a connection you must categorize the connecting party into one or more groups that can be used to establish the privacy access level to your information.</p><p>Sociabile also provides chatting features allowing you to share private messages with individuals or groups with whom you have made a connection. Only the parties engaged in the chat conversation can view content exchanged such as text, images, and video. You may also create a time capsule to share information at a future date with connections made. You may send the time capsule to yourself or any other users who have been connected through the application. Time capsules may contain text, images, videos, or animated emoticons. The contents of the time capsule can only be viewed on the date and time that the time capsule has been set to open.</p><p><u><b>Terms of Service</b></u></p><p><i>1.1 Agreement to Terms of Services</i></p><p>This is an agreement between Sociabile LLC, the owners and operators of http://www.scble.com (collectively, the “Services”), and you, the user of the service. BY DOWNLOADING AND USING SOCIABILE SERVICES, YOU ACKNOWLEDGE AND AGREE TO SOCIABILE’S TERMS OF SERVICES, INCLUDING SOCIABILE'S PRIVACY POLICY WHICH ARE INCORPORATED HEREIN BY REFERENCE. You may not use these Services unless you agree to the Terms of Service.</p><p>Sociabile may provide links to third-party websites that are not owned or controlled by Sociabile. Sociabile has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third-party websites. In addition, Sociabile will not and cannot censor or edit the content of any third-party site. By using the Service, you expressly acknowledge and agree that Sociabile shall not be responsible for any damages, claims or other liability arising from or related to your use of any third-party website.</p><p><i>1.2 Who May Contract</i></p><p>Persons under 13 years of age may not contract to use or use Sociabile. Individuals under 18 years of age may only use Sociabile with consent from the user’s parent or legal guardian. Individuals may not use Sociabile on behalf of or for third parties, except if the third party is a business enterprise and the individual has a right to contract on behalf of the business enterprise. If an individual is using Sociabile on behalf of or for the purpose of a business enterprise, then the business enterprise as well as the individual users shall be deemed to have agreed to and accepted the Sociabile Terms of Services. By downloading and using Sociabile, you are acknowledging that you are of proper age and/or have consent to contract for the use of Sociabile.</p><p><i>1.3 Sociabile Access</i></p><p>Subject to your compliance with these Terms of Service, Sociabile hereby grants you permission to use the Service, provided that: (i) your use of the Service as permitted is solely for your personal use, and you are not permitted to resell or charge others for use of or access to the Service, or in any other manner inconsistent with these Terms of Service; (ii) you will not duplicate, transfer, give access to, copy or distribute any part of the Service in any medium without Sociabile's prior written authorization; (iii) you will not attempt to reverse engineer, alter or modify any part of the Service; and (iv) you will otherwise comply with the terms and conditions of these Terms of Service and Privacy Policy.</p><p><i>1.4 Revocation of Access</i></p><p>Sociabile reserves the right to revoke access to you at any time and without notice if Sociabile has reason to believe that you are promoting or participating in any actions that are deemed as prohibited under the Sociabile Terms of Service. A representative list of prohibited activities is set forth in Section 4 herein, but may be amended at any time by Sociabile by providing five calendar day notice to users. </p><p>You agree not to use or launch any automated system that accesses the Service in a manner that sends more request messages to the Sociabile servers in a given period of time than a human can reasonably produce in the same period by using a Sociabile application.  You agree not to reverse-engineer our system, our protocols, or explore outside the boundaries of the normal requests made by Sociabile clients, and you may not use any tools designed to explore or harm, penetrate or test the site. You agree not to spam, or solicit for commercial purposes, any users of the Service. Any such activities allow Sociabile to immediately revoke your access.</p><p><u>2. Using Sociabile</u></p><p><i>2.1 Introduction / User Account</i></p><p>Sociabile is a private social network based on connectivity using your mobile phone number or unique userID. Users of this application may be referred to as Socialites, which means a person who is prominent, well known, and fond of social activities. Your userID will be automatically assigned to you but may be changed based on your preferences. Your userID must be unique and can only be changed once. </p><p><i>2.2 Connecting to others</i></p><p>To connect to another Socialite, you must know the other user’s phone number or unique userID. If you have another member’s phone number in your mobile device address book, then that person will be recommended to you as a friend. You may add them and determine which group(s) you want them to be associated with. Groups are used to control privacy settings and all users must be in at least one group. You may also add users by searching for their unique userID that they have provided to you. The search will only return a single result if there is an exact match. This enables users to only connect with others they wish to and keep their network private. If you have been added by another user as a friend then you will receive a friend request allowing you to accept or reject that friend connection.</p><p><i>2.3 Groups</i></p><p>There are predefined groups when you first begin using Sociabile. You may add more groups to further define your friend connections. All users added to your network must be in a minimum of one group but may be added to multiple groups. If you create a custom group, then add members to the group, then delete the group, the members will no longer be part of the group. If a user that you connected to was added only to the custom group that was deleted, then they will be left without a group and by default be added to the predefined group called “Acquaintances.” </p><p><i>2.4 Privacy Settings</i></p><p>Privacy settings are used to provide permission to content rather than explicitly blocking users. Privacy settings are based on groups and can be maintained in the Settings Tab. You may grant permission to groups enabling them to view your “My Mind” messages, Profile information and Images that you posted. You can determine which groups you want to grant access to this information and all members who belong to those groups will be able to see the content generated. </p><p>Socialites may also create custom privacy settings for each individual My Mind message or Image posted. When posting a My Mind message or Image, click the customer privacy settings icon and select which groups or individual friends you want to grant permission. Creating custom settings will replace the default privacy settings established in the Settings Tab. Therefore, the default privacy settings will not be used and only users select in the custom settings will be able to see that My Mind message or Image.</p><p>Images may also be explicitly shared with other Socialites that you have connected to by clicking the Shared feature. Any images shared with you will appear in the “Shared Pictures” section on your profile. The Shared Pictures section of your profile is private and cannot be seen by other users. </p><p><i>2.5 Chat</i></p><p>Sociabile provides chatting services for users to engage in private conversations. A user may only send a chat request to other users that they have connected with through the application. Only members participating in the chat conversation can see messages sent between members of the chat. Chat conversations may consist of text, images, static emoticons, or animated emoticons. Images sent may be seen or downloaded only by individuals participating in the chat conversation.</p><p>Emoticons may be used to express emotions during a chat conversation. Emoticons can be either static or dynamic. Static emoticons are characters that do not move. They convey various expressions to enhance the chatting experience. Animated emoticons provide action to further show the emotion desired. All characters are trademarked either by Sociabile or a third party that has provided the emoticon for sale on Sociabile. Purchasing emoticons gives the user privilege to use the emoticons as long as they participate on Sociabile, but all rights and ownership remain with the trademark owner. </p><p><i>2.6 Time Capsules</i></p><p>Time capsules are a special type of messaging system offered through Sociabile allowing you to capture a moment in time or send a message that is intended for future use. Time capsules may include text, images and emoticons. Time capsules can only be sent to individuals that the sender has established a connection with or to themselves. Users who receive a time capsule may only open the time capsule on the date and time that it has been set to open. The message and sender can only be viewed once a time capsule has been opened to be read.</p><p><i>2.7 Account Recovery</i></p><p>We at Sociabile realize that our users may purchase new phones or change their phone number from time to time. To assist users with transferring their account to a new device or phone number, we have created the account recovery service. The account recovery service requires your email address to setup. In Account Information on the Setting Tab, you can select the e-mail account recovery system to add an email to your account. Your email will NOT be used for spam and is NOT required to use Sociabile. Its purpose serves to assist you in recovering your account in the event you delete the application and need to install it again on the original device or a new mobile device. Sociabile is committed to protecting your privacy and personal information.</p><p><u>3. Intellectual Property Right</u></p><p>Sociabile respects your privacy and intellectual property rights. We are committed to providing a service that you may use for both business and personal use. Users will retain ownership and intellectual property rights of any content sent through Sociabile’s services. Furthermore, Sociabile will never provide personally identifiable information or intellectual property of our users to a third party except as may be required by law.</p><p>By adding content to the Sociabile site, you agree that you have acquired all intellectual property rights to the content and will fully indemnify Sociabile for any claim of infringement brought by third-parties.</p><p>The design of the Sociabile Service along with Sociabile created text, scripts, graphics, interactive features, and the trademarks, service marks and logos contained therein ('Marks'), are owned by or licensed to Sociabile, subject to copyright and other intellectual property rights under United States and foreign laws and international conventions. The Service is provided to you AS IS for your information and personal use only. Sociabile reserves all rights not expressly granted in and to the Service. You agree to not engage in the use, copying, or distribution of any of the Service other than expressly permitted herein, and are expressly prohibited from using the Service for any commercial purposes.</p><p>Sociabile is dedicated to constant improvement of its services to the community of users. It is our belief that the most valuable information for improvement comes from our users. If you (the user) posts any content on our Services, you are deemed to have granted permission to Sociabile and its partners to use, store, copy, modify, transmit, disclose, distribute, or create derived work. This license will continue perpetually even if the user ceases to use Sociabile or deactivates their account. To recapitulate this agreement, Sociabile and its partners may use content provided for the advancement of its services and will not divulge any personally identifiable information or intellectual property to a third party.</p><p><u>4. Prohibited Activities</u></p><p>Users may be removed from the Sociabile community for conducting acts that are deemed as prohibited or unlawful. By using Sociabile you agree to the Terms of Services and will not engage in activities that are prohibited. If you identify other users engaging in such activities it should be reported to Sociabile in order to be investigated and determine if action needs to be taken. Below is a list of activities that is deemed as prohibited.</p><ul><li>Misrepresenting yourself or impersonating other. This includes the creation of fake profiles or profiles that misrepresent who you are to others.</li><li>Name squatting. Name squatting consists of creating a user name or userID that infringes on the intellectual property rights, privacy rights or other rights entitled to a third party.</li><li>Sending unsolicited messages or spamming users. Creating an account for the purpose of advertising or spamming is prohibited.</li><li>Using our services for commercial purposes or the benefit of a third party</li><li>Engaging in illegal activity as identified by US law (drugs, money laundering, prostitution, conspiracy, etc).</li><li>Disseminating violent or sexual material that may lead to discomfort of other users.</li><li>Engaging in discrimination of other members (including but not limited to: ethnic, culture, gender, religious, sexual or social status).</li><li>Engaging in bullying or the creation of a hostile environment for other users.</li><li>Gambling or enticing others to participate in gambling.</li><li>Using or exploiting our intellectual property or the intellectual property of our users.</li><li>Accessing our services or collecting data from our services without consent. This includes the use of bots, crawlers, or other software used to collect and store data.</li><li>Engaging in social engineering to manipulate members of our community.</li><li>Intentionally distributing viruses, malware or other harmful software that could cause harm to our services or computing devices of our users.</li><li>Creating multiple accounts for disruptive or abusive purposes.</li><li>Engaging in activities that encourages others to breach Sociabile Terms of Service.</li></ul><p>Users who have been convicted of crimes against children or committing an act of terrorism are prohibited from using our services. Children under the age of 13 are restricted from using Sociabile.</p><p><u>5. Advertising</u></p><p>We are committed to providing an Ad-Free social networking environment. We hate third party advertisement as much as anybody and will not be providing unsolicited advertisements, banner ads, or links by third party entities. Additionally, your personally identifiable information will not be sold to third parties for the use of advertising. We are committed to maintaining your privacy and protecting our users.</p><p><u>6. Animated Emoticons</u></p><p>Sociabile grants its users permission to use paid-for content through Sociabile services. Content such as Emoticons are solely for the purpose of use through services provided by Sociabile. The artist/third party that provides content for use through Sociabile will retain all intellectual property rights to the content they develop. Users acquire the right to use content through Sociabile services and are prohibited from use or redistributing content outside of Sociabile services.</p><p><u>7. In-App Payments</u></p><p>Sociabile is a free service to our community of users who have agreed to the Terms of Service. However, Sociabile users may need to make payments from time to time for additional services such as the use of animated emoticons. Users are responsible for providing appropriate funds for in-app purchases and the use of digital content. </p><p><u>8. Location-Based Services</u></p><p>Sociabile intends to provide location-based services for special features that will be provided in future versions. Location-based services may be turned off by the user at any time without consequence to basic services provided by Sociabile. By disabling location-based services, special features that will be added will not be utilized by that users.</p><p><u>9. Service Interruptions and Maintenance</u></p><p>Sociabile is committed to providing perpetual service without interruption every day of the year. However, there may be times where interruptions beyond Sociabile’s control occur or interruptions that are necessary for maintenance to maintain a high level of service quality or improve our services. When possible, users will be notified in advance if such interruptions are scheduled to occur. In the even that an unforeseen events occur that interrupt service, Sociabile will notify the users of the event and action taken immediately after the issue is resolved. Users do not have a right to any compensation for any service interruption time.</p><p><u>10. Termination of Services</u></p><p>You may choose to terminate your use of Sociabile at any time by going to the Account Information page under the Settings Tab. Usage statistics will be archived for performance and usage monitoring to evaluate Sociabile traffic and activity for improvement of services. Once you have deleted your account it will no longer be recoverable. Any pictures or text that have been shared with friends will be removed from their shared folder and no longer visible, and any emoticons will be deleted from your account. </p><p><u>11. Third-Party Research</u></p><p>Sociabile is open for active participation in academic research that may advance theoretical contributions or improve services provided to customers. When such research is conducted, no identifying information will be used or provided to third parties. Third party research will produce aggregate and statistical data for all published research to evaluate usage, effective, and generalizable behaviors. We will restrict any personally identifiable information from being used in any research conducted through Sociabile.</p><p><u>12. Warranty Disclaimer</u></p><p>YOU AGREE THAT YOUR USE OF THE SOCIABILE SERVICE SHALL BE AT YOUR SOLE RISK. TO THE FULLEST EXTENT PERMITTED BY LAW, SOCIABILE, ITS OFFICERS, DIRECTORS, EMPLOYEES, AND AGENTS DISCLAIM ALL WARRANTIES, EXPRESS OR IMPLIED, IN CONNECTION WITH THE SERVICE AND YOUR USE THEREOF. SOCIABILE MAKES NO WARRANTIES OR REPRESENTATIONS ABOUT THE ACCURACY OR COMPLETENESS OF THIS SERVICE'S CONTENT AND ASSUMES NO LIABILITY OR RESPONSIBILITY AND SHALL NOT BE LIABLE TO YOU WHETHER BASED ON WARRANTY, CONTRACT, TORT, OR ANY OTHER LEGAL THEORY, AND WHETHER OR NOT THE COMPANY IS ADVISED OF THE POSSIBILITY OF SUCH DAMAGES, FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, PUNITIVE, OR CONSEQUENTIAL DAMAGES WHATSOEVER RESULTING FROM ANY (I) ERRORS, MISTAKES, OR INACCURACIES OF CONTENT, (II) PERSONAL INJURY OR PROPERTY DAMAGE, OF ANY NATURE WHATSOEVER, RESULTING FROM YOUR ACCESS TO AND USE OF OUR SERVICE, (III) ANY UNAUTHORIZED ACCESS TO OR USE OF OUR SERVERS AND/OR ANY AND ALL PERSONAL INFORMATION AND/OR FINANCIAL INFORMATION STORED THEREIN, (IV) ANY INTERRUPTION OR CESSATION OF TRANSMISSION TO OR FROM OUR SERVICE, (V) ANY BUGS, VIRUSES, TROJAN HORSES, OR THE LIKE WHICH MAY BE TRANSMITTED TO OR THROUGH OUR SERVICE THROUGH THE ACTIONS OF ANY THIRD PARTY, AND/OR (VI) ANY ERRORS OR OMISSIONS IN ANY CONTENT OR FOR ANY LOSS OR DAMAGE OF ANY KIND INCURRED AS A RESULT OF THE USE OF ANY CONTENT POSTED, EMAILED, TRANSMITTED, OR OTHERWISE MADE AVAILABLE VIA THE SOCIABILE SERVICE. SOCIABILE DOES NOT WARRANT, ENDORSE, GUARANTEE, OR ASSUME RESPONSIBILITY FOR ANY PRODUCT OR SERVICE ADVERTISED OR OFFERED BY A THIRD PARTY THROUGH THE SOCIABILE SERVICE OR ANY HYPERLINKED WEBSITE OR FEATURED IN ANY USER SUBMISSION OR OTHER ADVERTISING, AND SOCIABILE WILL NOT BE A PARTY TO OR IN ANY WAY BE RESPONSIBLE FOR MONITORING ANY TRANSACTION BETWEEN YOU AND THIRD-PARTY PROVIDERS OF PRODUCTS OR SERVICES. </p><p><u>13. Liability for Negligence</u></p><p>While Sociabile takes measures to protect our users, we do not guarantee that our services are free of de facto or legal flaw. Users should use these services at their own risk and bear all responsibility for their actions. SOCIABILE SHALL NOT BE HELD RESPONSIBLE FOR DAMAGES INFLICTED UPON USERS THROUGH SOCIABILE WHILE USING OUR SERVICES. If an unlawful act occurs, it is the user’s responsibility to notify Sociabile so that we can take action to prevent them from occurring. It is the responsibility of the community to work together and prevent restricted or unlawful acts from occurring. By using Sociabile you are agreeing to these terms and using the services at your own risk. </p><p><u>14. Indemnification</u></p><p>You agree to defend, indemnify and hold harmless Sociabile, its parent corporation, officers, directors, employees and agents, from and against any and all claims, damages, obligations, losses, liabilities, costs or debt, and expenses (including but not limited to attorney's fees) arising from: (i) your use of and access to the Sociabile Service; (ii) your violation of any term of these Terms of Service; (iii) your violation of any third party right, including without limitation any copyright, property, or privacy right; or (iv) any claim that one of your submissions caused damage to a third party. This defense and indemnification obligation will survive these Terms of Service and your use of the Sociabile Service</p><p><u>15. The Terms of Service are Subject to Change</u></p><p>As our services continue to grow, Sociabile may make changes to the Terms of Service. When revisions are made to the Terms of Service or privacy policy, Sociabile will send notifications alerting our community of users about those changes. By continuing to use Sociabile services, you are deemed to have accepted the updated Terms of Service.</p><p>We welcome your comments and feedback about our Terms of Service as well as other inquiries/recommendations that may improve our service to you. Your feedback is important! The next section below covers feedback as it pertains to our Terms of Service.</p><p><u>16. Contacting Socialites</u></p><p>We may at times send messages to our users informing them of updates or other information. Messages sent to our user community will appear in the Notifications section under the Settings Tab. Users may be contacted in order to information them of updates, new emoticons that are available, solicit feedback or response to a survey administered by Sociabile or other reasons deemed necessary. </p><p>Users may also provide feedback or contact us through the help menu. In this event, a personal response may be given through email to the user if an email is provided. Upon termination of service by the user, we may contact the user to inquire about their reasons for leaving the Sociabile community so that we may improve services to better serve our users.</p><p><u>17. Contacting Sociabile</u></p><p>User may contact us at Sociabile to provide feedback, report a problem, give a recommendation or submit an inquiry. Using the Help Menu under the settings Tab, the can select the contact option to send a message to Sociabile. The message may include text and an image. We want to hear from our users and will make every attempt to respond to all inquiries that need attention in a timely manner. However, a response is not guaranteed depending on the type of inquiry or volume of inquiries.</p><p>When submitting an inquiry through the mobile application, we will collect information to help us understand the inquiry or resolve any potential issues reported. User provide their intended message, email address (optional) and image (optional). Information about the user’s mobile device and model, operating system, current version of Sociabile, userID, user name and phone number will also be collected automatically to assist in handling the inquiry. This information will be used only for addressing the inquiry and will not be transmitted or reported to any third party or external entities. </p><p><u>18. Other Terms and Conditions</u></p><p><i>18.1 Legal Interpretation and Venue</i></p><p>The validity and interpretation of this Agreement and the legal relations of the parties to it shall be governed by the internal substantive laws of the State of New Mexico, U.S.A., exclusive of its conflict of law provisions. Unless the context otherwise requires, when used in this Agreement, the singular shall include the plural, the plural shall include the singular, and all nouns, pronouns and any variations thereof shall be deemed to refer to the masculine, feminine, or neuter, as the identity of the person or persons may require. The Parties agree that jurisdiction for any legal action brought related to this Agreement shall be limited to the courts of Bernalillo County (NM).</p><p><i>18.2 Dispute Resolution</i></p><p>In the event of any dispute under this Agreement, the parties shall attempt to resolve the dispute in a reasonable and equitable manner through negotiation within a reasonable time. In the event that a resolution cannot be achieved within a reasonable time, the parties agree to submit any disputes involving commercial issues to non-binding arbitration in a mutually agreeable time and location within the continental United States of America before the American Arbitration Association or an equivalent body selected by mutual agreement, in accordance with its Commercial Arbitration Rules. The parties agree that they shall be entitled to discovery in the same manner as though the dispute were within the jurisdiction of the Courts of the State of New Mexico, United States of America. Nothing in this provision shall preclude a party from obtaining temporary or permanent equitable or statutory relief from any court or other body as necessary to prevent irreparable damage to its interests or to prevent damages not adequately compensable in monetary terms.</p><p><i>18.3 Severability and Enforceability</i></p><p>Should any section, or portion thereof, of this Agreement be held invalid by reason of any law, statute or regulation existing now or in the future in any jurisdiction by any court of competent authority or by a legally enforceable directive of any governmental body, such section or portion thereof shall be validly reformed so as to reflect the intent of the parties as nearly as possible and, if unreformable, shall be deemed divisible and deleted with respect to such jurisdiction. In the event any provision of this Agreement is found to be unenforceable or invalid and unreformable, such provision shall be severable from this Agreement and shall not affect the enforceability or validity of any other provision contained in this Agreement.</p><p><i>18.4 Legal Fees</i></p><p>In the event of the commencement of suit to enforce any of the terms or conditions in this Agreement, each Party shall bear financial responsibility for their own attorney and legal fees regardless of which Party prevails.</p><p><i>18.5 Notices</i></p><p>All notices and other required communications under this Agreement shall be in writing, and shall be effective (a) when hand delivered, including delivery by messenger or courier service (or if delivery is refused, at the time of refusal), to the address set forth below, (b) when received or refused as evidenced by the postal receipt if sent by United States mail as Certified Mail, return receipt requested, with proper postage prepaid (or legal equivalent), addressed as set forth below or (c) when received digitally as evidenced by the delivered report of electronic mail: </p><p>Sociabile<br>6100 Cortaderia St. NE No. 3322<br>Albuquerque, New Mexico, 87111<br>Attn: Aaron French, President</p>";
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
        if( (!isset($post_data['group_ids']) && $header_data['os'] == 1) || !isset($post_data['type'])) {
            Library::logging('alert',"API : setPrivacySettings : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 1) {
                    $group_ids = json_decode($post_data['group_ids']);
                } else {
                    if( empty($post_data['group_ids']) ){
                        $group_ids = array();
                    }else{
                        $group_ids = $post_data['group_ids'];
                    }
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
               // print_r($user->my_mind_groups); exit();
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
                Library::logging('error',"API : setPrivacySettings, error_msg : ".$e." ".": user_id : ".$header_data['id']);
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
                $post_data['image_name']    = str_replace( FORM_ACTION, "", $post_data['image_name'] );
                if($header_data['os'] == 1) {
                    $group_ids =  json_decode($post_data['group_id']);
                } else {
                    $group_ids =  $post_data['group_id'];
                }
                $post                   = new Posts();
                $post->user_id          = $header_data['id'];
                $post->text             = $post_data['image_name'];
                $post->total_comments   = 0;
                $post->likes            = 0;
                $post->dislikes         = 0;
                $post->date             = time();
                $post->group_id         = $group_ids;
                $post->type             = 3;    // type| 1 for text posts, 2 for images, 3 for shared images
                if ($post->save() == false) {
                    foreach ($post->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : sharePhotos : ".$errors." user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
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
                $user = Users::findById($user_id);
                if(  !empty($user) && $user->is_active && ($user->is_deleted == 0) && $user->running_groups) {

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

                        $i          = 0;
                        $user_post  = array();
                        if($my_mind == 1) {
                            $posts = Posts::find(array(array("user_id" => $user_id, "type"=>1)));
                            if(is_array($posts)) {
                                foreach($posts as $post) {
                                    $isLiked    = false;
                                    $isDisliked = false;
                                    if( !empty($post->liked_by) && in_array( $header_data['id'], $post->liked_by) ){
                                        $isLiked    = true;
                                    }
                                    if( !empty($post->disliked_by) && in_array( $header_data['id'], $post->disliked_by) ){
                                        $isDisliked = true;
                                    }
                                    $user_post[$i]['post_id']               = (string)$post->_id;
                                    $user_post[$i]['user_name']             = $post->username;
                                    $user_post[$i]['post_text']             = $post->text;
                                    $user_post[$i]['post_comment_count']    = $post->total_comments;
                                    $user_post[$i]['post_like_count']       = $post->likes;
                                    $user_post[$i]['post_dislike_count']    = $post->dislikes;
                                    $user_post[$i]['is_liked']              = $isLiked;
                                    $user_post[$i]['is_disliked']           = $isDisliked;
                                    $user_post[$i]['post_timestamp']        = $post->date;
                                    $i++;
                                }
                            }
                        }
                        usort($user_post, function($postA, $postB){
                            if ($postA["date"] == $postB["date"]) {
                                return 0;
                            }
                            return ($postA["date"] < $postB["date"]) ? 1 : -1;
                        });       
                        
                        if($about_me == 1) {
                            $about_me_info['gender'] = isset($user->gender) ? $user->gender : '';
                            $about_me_info['hobbies'] = isset($user->hobbies) ? $user->hobbies : '';
                            $about_me_info['description'] = isset($user->about_me) ? $user->about_me : '';
                        } else {
                            $about_me_info['gender'] = '';
                            $about_me_info['hobbies'] = '';
                            $about_me_info['description'] = '';
                        }

                        $my_pictures_info   = array();
                        if($my_pictures == 1) {
                            $posts = Posts::find(array(array("user_id" => $user_id, "type"=>2)));
                            if(is_array($posts)) {
                                foreach($posts as $post) {
                                    $postId = (string)$post->_id;
                                    $isLiked    = false;
                                    $isDisliked = false;
                                    if( !empty($post->liked_by) && in_array( $header_data['id'], $post->liked_by) ){
                                        $isLiked    = true;
                                    }
                                    if( !empty($post->disliked_by) && in_array( $header_data['id'], $post->disliked_by) ){
                                        $isDisliked = true;
                                    }
                                    if( is_array($post->text) ){
                                        continue;
                                    }
                                    $my_pictures_info[$postId]['post_id']           = $postId;
                                    $my_pictures_info[$postId]['text']              = FORM_ACTION.$post->text;
                                    $my_pictures_info[$postId]['user_name']         = $user->username;
                                    $my_pictures_info[$postId]['total_comments']    = $post->total_comments;
                                    $my_pictures_info[$postId]['likes']             = $post->likes;
                                    $my_pictures_info[$postId]['dislikes']          = $post->dislikes;
                                    $my_pictures_info[$postId]['date']              = $post->date;
                                    $my_pictures_info[$postId]['is_liked']          = $isLiked;
                                    $my_pictures_info[$postId]['is_disliked']       = $isDisliked;
                                    $my_pictures_info[$postId]['multiple']          = 0;
                                }
                            }
                        }
                        usort($my_pictures_info, function($postA, $postB){
                            if ($postA["date"] == $postB["date"]) {
                                return 0;
                            }
                            return ($postA["date"] < $postB["date"]) ? 1 : -1;
                        });       
                        
                        $profile['image_url'] = FORM_ACTION;
                        $profile['mobile_no'] = $user->mobile_no;
                        $profile['username'] = $user->username;
                        $profile['context_indicator'] = $user->context_indicator;
                        $profile['birthday'] = isset($user->birthday) ? $user->birthday : '';
                        $profile['profile_pic'] = isset($user->profile_image) ? FORM_ACTION.$user->profile_image : 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg';

                        $result['profile']      = $profile;
                        $result['my_mind']      = $user_post;
                        $result['about_me']     = isset($about_me_info) ? $about_me_info : '';
                        $result['my_pictures']  = array_values($my_pictures_info);

                        Library::output(true, '1', "No Error", $result);
                    } else {
                        Library::output(false, '0', "Wrong User Id", null);
                    }

                } else {
                    Library::output(false, '0', INVALID_ID, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : getFriegndsInfo, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    /**
     * Method for get Images uploaded by user and shared with him by friends
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
            $db         = Library::getMongo();
            $userResult = $db->execute('return db.users.find({"_id":ObjectId("'.$header_data['id'].'")}).toArray()');
            if($userResult['ok'] == 0) {
                Library::logging('error',"API : getImages (get user info) , mongodb error: ".$userResult['errmsg']." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }    
            $user   = $userResult['retval'][0];
            
            if( $type == 1 ) { // type 1 for uploaded images
                $posts  = $db->execute('return db.posts.find( {"user_id":"'.$header_data['id'].'", "type":2} ).toArray()');
                if($posts['ok'] == 0) {
                    Library::logging('error',"API : getImages (get user info) , mongodb error: ".$posts['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $result     = array();
                foreach( $posts['retval'] As $postDetail ){
                    $isLiked    = false;
                    $isDisliked = false;
                    if( !empty($postDetail["liked_by"]) && in_array( $header_data['id'], $postDetail["liked_by"]) ){
                        $isLiked    = true;
                    }
                    if( !empty($postDetail["disliked_by"]) && in_array( $header_data['id'], $postDetail["disliked_by"]) ){
                        $isDisliked = true;
                    }
                    $postDetail["text"] = (!is_array($postDetail["text"])) ? FORM_ACTION.$postDetail["text"] : $postDetail["text"];
                    if( is_array($postDetail["text"]) ){
                        continue;
                    }
                    $username   = isset($user["username"]) ? $user["username"] : '';
                    $postId = (string)$postDetail["_id"];
                    $result[$postId]["post_id"]             = (string)$postDetail["_id"];
                    $result[$postId]["user_name"]           = $username;
                    $result[$postId]["text"]                = $postDetail["text"];
                    $result[$postId]["date"]                = $postDetail["date"];
                    $result[$postId]["likes"]               = $postDetail["likes"];
                    $result[$postId]["dislikes"]            = $postDetail["dislikes"];
                    $result[$postId]["total_comments"]      = $postDetail["total_comments"];
                    $result[$postId]["is_liked"]            = $isLiked;
                    $result[$postId]["is_disliked"]         = $isDisliked;
                    $result[$postId]["multiple"]            = 0;
                }
            }
            elseif( $type == 2 ) { // type 2 for share images
                
                $result     = array();
                 if(isset($user['running_groups'])) {
                     foreach ($user['running_groups'] as $groups) {
                        $friendsResult  = $db->execute('return db.users.find({"_id":ObjectId("'.$groups['user_id'].'")}).toArray()');
                        if($friendsResult['ok'] == 0) {
                            Library::logging('error',"API : getImages (get friends info) , mongodb error: ".$friendsResult['errmsg']." ".": user_id : ".$header_data['id']);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        } 
                        $friends_info   = $friendsResult['retval'][0];
                        if( empty($friends_info['running_groups']) ){
                            continue;
                        }
                        
                        // $friendsGroup will contain the groups in which friend has put the user
                        $friendsGroup   = array();
                        foreach($friends_info['running_groups'] as $grps) {
                            if( $grps["user_id"] == $header_data['id'] ){
                                $friendsGroup   = $grps["group_id"];
                                break;
                            }
                        }
                        
                        $posts  = $db->execute('return db.posts.find({"user_id":"'.(string)$friends_info['_id'].'", "type":3}).toArray()');
                        if($posts['ok'] == 0) {
                            Library::logging('error',"API : getImages (get user info) , mongodb error: ".$posts['errmsg']." ".": user_id : ".$header_data['id']);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        }
                        foreach( $posts['retval'] As $postDetail ){ 
                            if( count(array_intersect($friendsGroup, $postDetail["group_id"])) ){
                                $isLiked    = false;
                                $isDisliked = false;
                                if( !empty($postDetail["liked_by"]) && in_array( $header_data['id'], $postDetail["liked_by"]) ){
                                    $isLiked    = true;
                                }
                                if( !empty($postDetail["disliked_by"]) && in_array( $header_data['id'], $postDetail["disliked_by"]) ){
                                    $isDisliked = true;
                                }
                                $postDetail["text"] = (!is_array($postDetail["text"])) ? FORM_ACTION.$postDetail["text"] : $postDetail["text"];
                                if( is_array($postDetail["text"]) ){
                                    continue;
                                }
                                $username   = isset($friends_info["username"]) ? $friends_info["username"] : '';
                                $postId     = (string)$postDetail["_id"];
                                $result[$postId]["post_id"]             = (string)$postDetail["_id"];
                                $result[$postId]["user_name"]           = $username;
                                $result[$postId]["text"]                = $postDetail["text"];
                                $result[$postId]["date"]                = $postDetail["date"];
                                $result[$postId]["likes"]               = $postDetail["likes"];
                                $result[$postId]["dislikes"]            = $postDetail["dislikes"];
                                $result[$postId]["total_comments"]      = $postDetail["total_comments"];
                                $result[$postId]["is_liked"]            = $isLiked;
                                $result[$postId]["is_disliked"]         = $isDisliked;
                                $result[$postId]["multiple"]            = 0;
                            }
                        }
                     }
                 }
             } else {
                 Library::output(false, '0', WRONG_TYPE, null);
             }
             
            Library::output(true, '1', "No Error", array_values($result) );
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
            $post_data['unique_id'] = strtolower($post_data['unique_id']);
            try {
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                if ($security->checkHash($post_data['password'], $user->password) && $user->unique_id == $post_data['unique_id']) {
                    Library::output(true, '1', USER_LOGIN, null);
                } else {
                    Library::output(false, '0', INVALID_LOGIN, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : userLogin, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    public function uploadChatImageAction($header_data,$post_data)
    {
        if( empty($_FILES["images"]['name']) ) {
            Library::logging('alert',"API : uploadChatImage : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                foreach( $_FILES["images"]['name'] As $key=>$value ){
                    $post_data['images'][]  = array( "name"=>$value, "tmp_name"=>$_FILES["images"]["tmp_name"][$key]) ;
                }
                $result = array();
                $amazon = new AmazonsController();
                $count  = 0;
                foreach( $post_data['images'] As $image ){
                    $uploadFile = rand().$image["name"];
                    $amazonSign = $amazon->createsignatureAction($header_data,10);
                    $url        = $amazonSign['form_action'];
                    $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                    $ext        = explode(".", $uploadFile);
                    $extension  = trim(end($ext));
                    if( !in_array($extension, array("jpeg", "png", "gif"))){
                        $extension  = "jpeg";
                    }
                    $postfields = array(
                        "key"                       => "chat/".$uploadFile,
                        "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                        "acl"                       => $amazonSign["acl"],
                        "success_action_redirect"   => $amazonSign["success_action_redirect"],
                        "policy"                    => $amazonSign["policy"],
                        "signature"                 => $amazonSign["signature"],
                        "Content-Type"              => "image/$extension",
                        "file"                      => file_get_contents($image["tmp_name"])
                    );
                    $ch = curl_init();
                    $options = array(
                        CURLOPT_URL         => $url,
                        CURLOPT_POST        => 1,
                        CURLOPT_HTTPHEADER  => $headers,
                        CURLOPT_POSTFIELDS  => $postfields,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true
                    ); // cURL options
                    curl_setopt_array($ch, $options);
                    $imageName      = curl_exec($ch);
                    curl_close($ch);
                    
//                    $postfields["key"] = "thumbnail/".$uploadFile;
//                    $postfields["file"] = $amazon->createThumbnail(FORM_ACTION.$imageName);
//                    $ch = curl_init();
//                    $options[CURLOPT_POSTFIELDS]    = $postfields;
//                    curl_setopt_array($ch, $options);
//                    $thumbnailName  = curl_exec($ch);
//                    curl_close($ch);
                    $result[]   = array( "image"=>FORM_ACTION.$imageName/*, "thumbnail"=>FORM_ACTION.$thumbnailName*/ );
                }
                Library::output(true, '1', POST_SAVED, $result);
            } catch (Exception $e) {
                Library::logging('error',"API : uploadChatImage : ".$e." ".": user_id : ".$header_data["id"]);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    public function uploadMultipleImagesAction($header_data,$post_data)
    {
//        print_r($_FILES); exit;
        if( empty($_FILES["images"]['name']) ) {
            Library::logging('alert',"API : sharePhotos : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                foreach( $_FILES["images"]['name'] As $key=>$value ){
                    $post_data['images'][]  = array( "name"=>$value, "tmp_name"=>$_FILES["images"]["tmp_name"][$key]) ;
                }
                $post                   = new Posts();
                $post->user_id          = $header_data["id"];
                $post->text             = array();
                $post->total_comments   = 0;
                $post->likes            = 0;
                $post->dislikes         = 0;
                $post->date             = time();
                $post->type             = 2;    // type| 1 for text posts, 2 for images
                if ($post->save() == false) {
                    foreach ($post->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : uploadMultipleImages : ".$errors." user_id : ".$header_data["id"]);
                    Library::output(false, '0', $errors, null);
                }
                $db     = Library::getMongo();
                $result = array();
                foreach( $post_data['images'] As $image ){
                   // $image_name = $image["file"];
                    $uploadFile = rand().$image["name"];
                    $amazon     = new AmazonsController();
                    $amazonSign = $amazon->createsignatureAction($header_data,10);
                    $url        = $amazonSign['form_action'];
                    $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                    $ext        = explode(".", $uploadFile);
                    $extension  = trim(end($ext));
                    if( !in_array($extension, array("jpeg", "png", "gif"))){
                        $extension  = "jpeg";
                    }
                    $postfields = array(
                        "key"                       => "uploaded/".$uploadFile,
                        "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                        "acl"                       => $amazonSign["acl"],
                        "success_action_redirect"   => $amazonSign["success_action_redirect"],
                        "policy"                    => $amazonSign["policy"],
                        "signature"                 => $amazonSign["signature"],
                        "Content-Type"              => "image/$extension",
                        "file"                      => file_get_contents($image["tmp_name"])
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
                    $imageName      = curl_exec($ch);
                    curl_close($ch);
                    $createdAt  = time();
                    $update = $db->execute('db.posts.insert( { "user_id" : "'.$header_data["id"].'", text: "'.$imageName.'", total_comments:0, likes:0, dislikes:0, date: "'.$createdAt.'", type:2 } )');
                    if( $update['ok'] == 0 ){
                        Library::logging('error',"API : uploadMultipleImages, mongodb error: ".$update['errmsg']." : user_id : ".$header_data["id"]);
                        Library::output(false, '0', "Unable to update images", null);
                    }
                    $res    = $db->execute('return db.posts.find( { "user_id" : "'.$header_data["id"].'", text: "'.$imageName.'", total_comments:0, likes:0, dislikes:0, date: "'.$createdAt.'", type:2 } ).toArray()');
                    if( $res['ok'] == 0 ){
                        Library::logging('error',"API : uploadMultipleImages, mongodb error: ".$res['errmsg']." : user_id : ".$header_data["id"]);
                        Library::output(false, '0', "Unable to update images", null);
                    }
                    $postId         = (string)$res["retval"][0]["_id"];
                    $post->text[]   = $postId;
                    $postArr        = array();
                    $postArr["post_id"]         = $postId;
                    $postArr["user_id"]         = $header_data["id"];
                    $postArr["text"]            = FORM_ACTION.$imageName;
                    $postArr["date"]            = $createdAt;
                    $postArr["likes"]           = 0;
                    $postArr["dislikes"]        = 0;
                    $postArr["total_comments"]  = 0;
                    $postArr["is_liked"]        = false;
                    $postArr["is_disliked"]     = false;
                    $postArr["post_type"]       = 2;
                    $result[]                   = $postArr;
                    if ($post->save() == false) {
                        foreach ($post->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : uploadMultipleImages : ".$errors." user_id : ".$header_data["id"]);
                        Library::output(false, '0', $errors, null);
                    }
                }
                Library::output(true, '1', POST_SAVED, $result);
            } catch (Exception $e) {
                Library::logging('error',"API : createPost : ".$e." ".": user_id : ".$header_data["id"]);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }

    function sendNotifications( $deviceToken, $message, $os ){
        if( !is_array($deviceToken) ){
            $deviceToken    = array( $deviceToken );
        }
        // prep the bundle
        if($os=='ios'){
            $file_path = dirname(__FILE__).'/certificates/ScblFinal.pem';
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', $file_path);
            stream_context_set_option($ctx, 'ssl', 'passphrase', APN_PASSPHRASE);
            $tmpMsg = json_decode($message["message"]);
            $body['aps'] = array(
                    'alert' => $tmpMsg->message,
                    'sound' => 'default'
                    );
            $body["data"] = $message["message"];
            foreach($deviceToken as $token){
                $err    = $errstr   = '';
                $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 120, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
                if(!$fp){
                    Library::logging('error',"API : sendNotifications : Unable to send push notification(Failed to connect $err $errstr) : message : ".$message["message"]);
                } 
                $payload    = json_encode($body);
                $msg        = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
                $res        = fwrite($fp, $msg);
                fclose($fp);
                if($res != 0 && $res != FALSE){
                } else {
                    Library::logging('error',"API : sendNotifications : Unable to send push notification : message : ".$message["message"]);
                }
            }
        }elseif( $os == 'android' ){
            $msg = array
            (
                    'message' 	=> $message["message"]
            );
            $fields = array
            (
                    'registration_ids' 	=> $deviceToken,
                    'data'		=> $msg
            );
            $headers = array
            (
                    'Authorization: key=' . GCM_API_ACCESS_KEY,
                    'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            curl_close( $ch );
            $resultArr  = (array)json_decode($result);
            if( empty($resultArr["success"]) ){
                    Library::logging('error',"API : sendNotifications : Unable to send push notification : ($result) : message : ".$message["message"]);
           }
        }
    }

    function sendNotification($headerdata, $post_data){
        //print_r($post_data); exit("test");
            $this->sendNotifications( $post_data["token"], array("message"=>$post_data["message"]), $post_data["os"] );
    }
    
}
?>
