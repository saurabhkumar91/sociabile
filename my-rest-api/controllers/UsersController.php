<?php
 
class UsersController 
{   

    /**
     * Method for registration on ejabberd server
     * @param $mobile_no request params
     * @param $jaxlPassword reponse object
     *
     * @author Saurabh Kumar
     * @return array containing jaxl_id and jaxl_password
     */
    
    function registerOnEjabberd($mobile_no,$jaxlPassword){
        require 'components/JAXL3/jaxl.php';
        require 'components/JAXL3/register.php';
        $client = new JAXL(array(
                'jid' => JAXL_HOST_NAME,
                'log_level' => JAXL_DEBUG
        ));

        $client->require_xep(array(
                '0077'	// InBand Registration	
        ));
        $form=array( "username"=>$mobile_no, "password"=>$jaxlPassword );
        $client->add_cb('on_stream_features', function($stanza) {
                $client = $_SESSION["client"];
                $client->xeps['0077']->get_form(JAXL_HOST_NAME);
                return "wait_for_register_form";
        });

        $client->add_cb('on_disconnect', function() {
        });
        $_SESSION["client"]  = $client;
        $_SESSION["form"]  = $form;
        // finally start configured xmpp stream
        $client->start();
        $form = $_SESSION["form"];
        unset($_SESSION["client"]);
        unset($_SESSION["form"]);
        if( isset($form['type']) ) {
            if($form['type'] == 'result') { //if registered successfully
                return array("jaxl_id"=>$form["username"].'@'.JAXL_HOST_NAME, "jaxl_password"=>$jaxlPassword);
            }else{ //if not registered successfully with an error
                Library::logging('error',"API : registration : JAXL registration failed with error ".$form['type'].". ".$mobile_no);
                Library::output(false, '0', JAXL_REG_FAILED, null); 
            }
        }else{ //if not registered successfully with unknown error
            Library::logging('error',"API : registration : JAXL registration failed ".$mobile_no);
            Library::output(false, '0', JAXL_REG_FAILED, null);
        }
    }
    
    /**
     * Method for new user registration
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function registrationAction($data){   
        try {
            if(!isset($data['mobile_no']) || !isset($data['device_id'])) {
                Library::logging('alert',"API : registration : ".ERROR_INPUT);
                Library::output(false, '0', ERROR_INPUT, null);
            } else {
                $result = array();
                $mobile_no = $data['mobile_no'];
                $device_id = $data['device_id'];
                $record = Users::find(array(array("mobile_no"=>$mobile_no)));
                $jaxlPassword           = "12345";
                if(count($record) > 0) {
                    $result['user_id'] = $record[0]->_id;
                    $result['otp'] = 1234;
                    if( empty($record[0]->jaxl_id) ){
                        $jaxlCredentials        = $this->registerOnEjabberd( $mobile_no, $jaxlPassword );
                        $db         = Library::getMongo();
                        $db->execute('return db.users.update({"_id" :ObjectId("'.$record[0]->_id.'") },{$set:{jaxl_id : "'.$jaxlCredentials["jaxl_id"].'",jaxl_password:"'.$jaxlCredentials["jaxl_password"].'"}})');
                        $result = array_merge( $result, $jaxlCredentials ); 
                    }else{
                        $result["jaxl_id"]          = $record[0]->jaxl_id;
                        $result["jaxl_password"]    = $jaxlPassword;
                    }
                    Library::output(true, '1', OTP_SENT, $result);
                } else {
                    $user   = new Users();
                    
                    $user->mobile_no        = $mobile_no;
                    $user->otp              = 1234;
                    $user->device_id        = $device_id;
                    $user->date             = time();
                    $user->is_active        = 0;
                    $user->profile_image    = DEFAULT_PROFILE_IMAGE;
                    
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : registration : ".$errors." ".$mobile_no);
                        Library::output(false, '0', $errors, null);
                    } else {
                        
                        /**************** code to register user on ejabber server ********************************/
                        $jaxlCredentials        = $this->registerOnEjabberd( $mobile_no, $jaxlPassword );
                        $user->jaxl_id          = $jaxlCredentials["jaxl_id"];
                        $user->jaxl_password    = $jaxlCredentials["jaxl_password"];
                        $user->save();
                        $result = array_merge( $result, $jaxlCredentials);
                        /************* register code end ********************************/
                        
                        $result['user_id'] = $user->_id;
                        $result['otp'] = 1234;
                        Library::output(true, '1', OTP_SENT, $result);
                    }
                }
            }
        } catch (Exception $e) {
            Library::logging('error',"API : registration : ".$e." ".$data['mobile_no']);
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
    
    public function generateTokenAction($header_data,$data){    
        if( !isset($data['device_id']) ) {
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
                    $user->hash         = $hash;
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
    
    public function codeVerificationAction($header_data,$data){
       if( !isset($data['otp_no'])) {
            Library::logging('alert',"API : codeVerification : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user_id = $header_data['id'];
                $otp_no = $data['otp_no'];
                $user = Users::findById($user_id);
                
                $group = array();
                $db = Library::getMongo();
                $groups = $db->execute('return db.groups.find( { $and: [ { is_active: 1 }, { group_name: "Friends" } ] } ).toArray()');
                
                if($groups['ok'] == 0) {
                    Library::logging('error',"API : codeVerification (get groups) mongodb error: ".$groups['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                array_push($group,(string)$groups['retval'][0][_id]);
                if($user->otp == $otp_no) {
                    if($user->is_active == 1) {
                        // user already exist
                    } else {
                        $user->is_active = 1;
                        $user->username = "user";
                        $user->context_indicator = "Available";
                        $user->my_mind_groups = $group;
                        $user->about_me_groups = $group;
                        $user->my_pictures_groups = $group;
                        $user->unique_id = uniqid();
                        $user->is_edit = 0;
                        $user->is_searchable = 0;
                        $user->is_mobile_searchable = 0;
                        $user->save();
                    }
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
     * Method to set device token (token used to send push notification to device)
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Saurabh Kumar
     * @return json
     */
    
    public function setDeviceTokenAction($header_data,$data){
       if( !isset($data['device_token']) ) {
            Library::logging('alert',"API : setDeviceToken : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
        try{
            $user = Users::findById($header_data['id']);
            $user->device_token = $data['device_token']; // token used to send push notification to device
            $user->os           = $header_data["os"];
            if( $user->save() ){
                Library::output(true, '1', DEVICE_TOKEN_UPDATED, null );
            }else{
               foreach ($user->getMessages() as $message) {
                   $errors[] = $message->getMessage();
               }
               Library::logging('error',"API : setDeviceToken : ".$errors." : user_id : ".$header_data['id']);
               Library::output(false, '0', ERROR_REQUEST, null);
            }
        } catch (Exception $e) {
            Library::logging('error',"API : setDeviceToken : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
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
    
    public function sendContactsAction($header_data,$post_data){   
       if(!isset($post_data['contact_numbers'])) {
            Library::logging('alert',"API : sendContacts : ".ERROR_INPUT.": user_id : ".$header_data['id'].' '.$post_data['contact_numbers']);
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
    
    public function setDisplayNameAction($header_data,$name){ 
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
    
    public function getProfileAction($header_data){ 
        $user_id = $header_data['id'];
        try {
            $result = array();
            $profile = array();
            $my_mind = array();
            $about_me = array();
            $user = Users::findById($user_id);
            $posts = Posts::find(array(array('user_id' => $user_id, "type"=>1)));
            $email_id = array();
            $profile['mobile_no'] = $user->mobile_no;
            $profile['username'] = $user->username;
            $profile['context_indicator'] = $user->context_indicator;
            $profile['birthday'] = isset($user->birthday) ? $user->birthday : '';
            $profile['profile_pic'] = FORM_ACTION.$user->profile_image;
            $profile['email_id'] = isset($user->email_id) ? $user->email_id : $email_id;
            $profile['password'] = isset($user->password) ? $user->password : '';
            $profile['unique_id'] = isset($user->unique_id) ? $user->unique_id : '';
            $profile['is_edit'] = isset($user->is_edit) ? $user->is_edit : '';
            $profile['is_searchable'] = isset($user->is_searchable) ? $user->is_searchable : '';
            $profile['is_mobile_searchable'] = isset($user->is_mobile_searchable) ? $user->is_mobile_searchable : '';
            
            $i = 0; 
            foreach($posts as $post) {
                $isLiked    = false;
                $isDisliked = false;
                if( !empty($post->liked_by) && in_array( $header_data['id'], $post->liked_by) ){
                    $isLiked    = true;
                }
                if( !empty($post->disliked_by) && in_array( $header_data['id'], $post->disliked_by) ){
                    $isDisliked = true;
                }
                $my_mind[$i]['post_id']             = (string)$post->_id;
                $my_mind[$i]['post_text']           = $post->text;
                $my_mind[$i]['post_timestamp']      = $post->date;
                $my_mind[$i]['post_like_count']     = $post->likes;
                $my_mind[$i]['post_dislike_count']  = $post->dislikes;
                $my_mind[$i]['post_comment_count']  = $post->total_comments;
                $my_mind[$i]["is_liked"]            = $isLiked;
                $my_mind[$i]["is_disliked"]         = $isDisliked;
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
    
    public function getIndicatorsAction(){ 
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
    
     public function setProfileAction($header_data,$post_data){ 
        if( !isset($post_data['username']) || !isset($post_data['birthday']) || !isset($post_data['gender']) || !isset($post_data['hobbies']) || !isset($post_data['about_me'])) {
            Library::logging('alert',"API : setProfile : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
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
    
     public function setContextIndicatorAction($header_data,$context){
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
    
     public function getRegisteredNumbersAction($header_data){
         try {
            $user = Users::findById($header_data['id']);
            if($header_data['os'] == 1) {
                $contact_numbers =  json_decode($user->contact_numbers);
            } else {
                $contact_numbers =  $user->contact_numbers;
            }
            $i          = 0;
            $register   = array();
            foreach($contact_numbers as $contacts) {
                $get_contacts = str_replace(' ', '', $contacts); 
                $get_contacts = str_replace('+91', '', $contacts); 
                if(substr($get_contacts, 0, 1) == "0") {
                    $get_contacts   = substr($get_contacts, 1);
                    //$get_contacts = preg_replace('/0/', '', $get_contacts, 1); 
                }
                $filter_contacts= preg_replace('/[^0-9\-]/', '', $get_contacts);
                $filter_contacts = str_replace('-', '', $filter_contacts); 
                if( $user->mobile_no == $filter_contacts ){
                    continue;
                }
                $record = Users::find(array("conditions" =>array("mobile_no"=>$filter_contacts,"is_active"=>1)));
                if(!empty($record)) {
                    
                    if( !empty($user->hidden_contacts) && in_array((string)$record[0]->_id, $user->hidden_contacts) ){
                        continue;
                    }
                    if(empty($record[0]->is_mobile_searchable)) {
                        continue;
                    }
                    if(isset($user->running_groups)) {
                        $isFriend   = false;
                        foreach($user->running_groups as $user_ids) {
                            if($user_ids['user_id'] == (string)$record[0]->_id) {
                                $isFriend   = true; 
                                break;
                            }
                        }
                        if( $isFriend ){
                            continue;
                        }
                    }
                    
                    $register[$i]['mobile_no'] = $contacts;
                    $register[$i]['user_id'] = (string)$record[0]->_id;
                    $register[$i]['username'] = $record[0]->username;
                    $register[$i]['jaxl_id'] = $record[0]->jaxl_id;
                    $register[$i]['profile_image'] = isset($record[0]->profile_image) ? FORM_ACTION.$record[0]->profile_image : 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg';
                    $register[$i]['request_sent'] = 0;
                    if(isset($user->request_sent)) {
                        foreach($user->request_sent as $request_sent) {
//                            if($request_sent['is_active'] == 1) {
//                                $j = 1;
//                            }
                            if($request_sent['user_id'] == (string)$record[0]->_id) {
                                $register[$i]['request_sent'] = 1;
                                $register[$i]['is_active'] = $request_sent['is_active'];
                                break;
                            }
                        }
                    }
                    $i++;
                }
            }
            if(empty($register)) {
                $result = array();
                Library::output(true, '1', "No Error", $result);
            } else {
                Library::output(true, '1', "No Error", $register);
            }
        } catch (Exception $e) {
            Library::logging('error',"API : setContextIndicator : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
     
    /**
     * Method for get email
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
     public function getEmailAction($header_data){
        try {
             $db = Library::getMongo();
             $email = $db->execute('return db.users.find({_id:ObjectId("'.$header_data['id'].'")},{email_id:1}).toArray()');
             if($email['ok'] == 0) {
                Library::logging('error',"API : getEmail , mongodb error: ".$email['errmsg']." : user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
             }
            if(isset($email['retval'][0]['email_id'])) {
                $result['email'] = $email['retval'][0]['email_id'];
                Library::output(true, '1', "No Error", $result);
            } else {
                $result['email'] = '';
                Library::output(true, '1', "No Error", $result);
            }
        } catch (Exception $e) {
            Library::logging('error',"API : getEmail : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
     
    /**
     * Method for edit unique user id
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
     public function editUniqueIdAction($header_data,$post_data){
         if(!isset($post_data['unique_id'])) {
            Library::logging('alert',"API : editUniqueId : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $db = Library::getMongo();
                
                $user_info = $db->execute('return db.users.find({"_id" :ObjectId("'.$header_data['id'].'") }).toArray()');
                if($user_info['ok'] == 0) {
                    Library::logging('error',"API : editUniqueId (user info) , mongodb error: ".$user_info['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                
                if($user_info['retval'][0]['is_edit'] == 0) {
                    
                     $user_unique_id = $post_data['unique_id'];
                    $ids = $db->execute('return db.users.find({},{unique_id:1,_id:0}).toArray()');
                    if($ids['ok'] == 0) {
                        Library::logging('error',"API : editUniqueId (get ids) , mongodb error: ".$ids['errmsg']." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }

                    foreach($ids['retval'] as $unique_id) {
                        if($user_unique_id == $unique_id['unique_id']) {
                            Library::output(false, '0', UNIQUE_USER_ID, null);
                        }
                    }

                    $update_id = $db->execute('return db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$set:{unique_id : "'.$user_unique_id.'",is_edit:1}})');
                    if($update_id['ok'] == 0) {
                        Library::logging('error',"API : editUniqueId (update id), mongodb error: ".$update_id['errmsg']." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }

                    Library::output(true, '1', UNIQUE_USER_UPDATED, null);
                } else {
                    Library::output(false, '0', UNIQUE_USER_ALREADY_SET, null);
                }
                   
            } catch(Exception $e) {
                Library::logging('error',"API : editUniqueId : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
     }
     
    /**
     * Method for set searchable unique id
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
     public function isSearchableAction($header_data,$type){
         try {
             $db = Library::getMongo();
             if($type == 1) { // is searchable true
                $update_id = $db->execute('return db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$set:{is_searchable : 1}})');
                if($update_id['ok'] == 0) {
                    Library::logging('error',"API : isSearchable (type 1), mongodb error: ".$update_id['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', "Updated Successfully", null);
                
             } elseif($type == 0) { // is searchable false
                $update_id = $db->execute('return db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$set:{is_searchable : 0}})');
                if($update_id['ok'] == 0) {
                    Library::logging('error',"API : isSearchable (type 0), mongodb error: ".$update_id['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', "Updated Successfully", null);
                
             } else {
                 Library::output(false, '0', WRONG_TYPE, null);
             }
        } catch(Exception $e) {
            Library::logging('error',"API : isSearchable : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
     
     
    /**
     * Method for set searchable mobile no
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Saurabh Kumar
     * @return json
     */
    
     public function isMobileSearchableAction($header_data,$type){
         try {
             $db = Library::getMongo();
             if($type == 1) { // is searchable true
                $update_id = $db->execute('return db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$set:{is_mobile_searchable : 1}})');
                if($update_id['ok'] == 0) {
                    Library::logging('error',"API : isMobileSearchable (type 1), mongodb error: ".$update_id['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', "Updated Successfully", null);
                
             } elseif($type == 0) { // is searchable false
                $update_id = $db->execute('return db.users.update({"_id" :ObjectId("'.$header_data['id'].'") },{$set:{is_mobile_searchable : 0}})');
                if($update_id['ok'] == 0) {
                    Library::logging('error',"API : isMobileSearchable (type 0), mongodb error: ".$update_id['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', "Updated Successfully", null);
                
             } else {
                 Library::output(false, '0', WRONG_TYPE, null);
             }
        } catch(Exception $e) {
            Library::logging('error',"API : isMobileSearchable : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
     
    /**
     * Method for searching user based on unique id
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
     public function searchUserAction($header_data,$unique_id){
         try {
             if(empty($unique_id)) {
                 Library::output(false, '0', WRONG_UNIQUE_ID, null);
             } else {
                $db = Library::getMongo();
                
                $user_info = $db->execute('return db.users.find({"unique_id" : "'.$unique_id.'", is_searchable : 1, is_active:1 }).toArray()');
                if($user_info['ok'] == 0) {
                    Library::logging('error',"API : searchUser (user info) , mongodb error: ".$user_info['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                if(isset($user_info['retval'][0])) {
                    $result['id'] = (string)$user_info['retval'][0]['_id'];
                    $result['username'] = $user_info['retval'][0]['username'];
                    $result['profile_pic'] = FORM_ACTION.$user_info['retval'][0]["profile_image"];
                    Library::output(true, '1', "No Error", $result);
                } else {
                     Library::output(false, '0', NO_USER_FOUND, null);
                }
             }
        } catch(Exception $e) {
            Library::logging('error',"API : searchUser : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
     
    /**
     * Method for searching user based on mobile no
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Saurabh Kumar
     * @return json
     */
    
     public function searchUserByMobileAction($header_data,$mobileNo){
         try {
             if(empty($mobileNo)) {
                 Library::output(false, '0', ERROR_INPUT, null);
             } else {
                $db = Library::getMongo();
                $user_info = $db->execute('return db.users.find({"mobile_no" : "'.$mobileNo.'", is_mobile_searchable : 1, is_active : 1 }).toArray()');
                if($user_info['ok'] == 0) {
                    Library::logging('error',"API : searchUserByMobileNo (user info) , mongodb error: ".$user_info['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                if(isset($user_info['retval'][0])) {
                    $result['id'] = (string)$user_info['retval'][0]['_id'];
                    $result['username'] = $user_info['retval'][0]['username'];
                    $result['profile_pic'] = FORM_ACTION.$user_info['retval'][0]["profile_image"];
                    Library::output(true, '1', "No Error", $result);
                } else {
                     Library::output(false, '0', NO_USER_FOUND, null);
                }
             }
        } catch(Exception $e) {
            Library::logging('error',"API : searchUserByMobileNo : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
     
    /**
     * Method for set display name
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Saurabh kumar
     * @return json
     */
    
     public function setProfileImageAction( $header_data, $post_data ){ 
        if( empty($post_data['image']) ) {
            Library::logging('alert',"API : setProfileImage : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $uploadFile = $post_data['image'];

                $amazon     = new AmazonsController();
                $amazonSign = $amazon->createsignatureAction($header_data,10);
                $url        = $amazonSign['form_action'];
                $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                $img        = explode("/", $uploadFile);
                $imgName    = end($img);
                $ext        = explode(".", $imgName);
                $extension  = trim(end($ext));
                if( !in_array($extension, array("jpeg", "png", "gif"))){
                    $extension  = "jpeg";
                }
                $postfields = array(
                    "key"                       =>  "profiles/".$imgName,//$amazonSign["key"],
                    "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                    "acl"                       => $amazonSign["acl"],
                    "success_action_redirect"   => $amazonSign["success_action_redirect"],
                    "policy"                    => $amazonSign["policy"],
                    "signature"                 => $amazonSign["signature"],
                    "Content-Type"              => "image/$extension",
                    "file"                      => file_get_contents($uploadFile)
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
                $result['image']    = FORM_ACTION.$imageName;
                curl_close($ch);
                if(is_string ( $imageName )){
                    $user = Users::findById($header_data['id']);
                    $user->profile_image = $imageName;
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : setProfileImage amazon controller : ".$errors." : user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    } else {
                        Library::output(true, '1', USER_PROFILE_IMAGE, $result );
                    }
                }else{
                    Library::logging('error',"API : setProfileImage : ".ERROR_REQUEST." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                    
            } catch (Exception $e) {
                Library::logging('error',"API : setProfileImage : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }  
        }
     }
    
     public function deleteProfileImageAction( $header_data ){ 
        try {
                $user = Users::findById($header_data['id']);
                if($user->profile_image    == DEFAULT_PROFILE_IMAGE){
                    Library::logging('error',"API : deleteProfileImage amazon controller : ".NO_PROFILE_IMAGE." : user_id : ".$header_data['id']);
                    Library::output(false, '0', NO_PROFILE_IMAGE, null);
                }
                require 'components/S3.php';
                $s3         = new S3(AUTHKEY, SECRETKEY);
                $bucketName = S3BUCKET;
                if ( ! $s3->deleteObject($bucketName, $user->profile_image) ) {
                    Library::logging('error',"API : deleteProfileImage : PROFILE IMAGE's FILE NOT DELETED FROM S3 Server : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', PROFILE_IMAGE_NOT_DELETED, null);
                }
                $user->profile_image = DEFAULT_PROFILE_IMAGE;
                if ($user->save() == false) {
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : deleteProfileImage amazon controller : ".$errors." : user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                }
                Library::output(true, '1', PROFILE_IMAGE_DELETED, null );

        } catch (Exception $e) {
            Library::logging('error',"API : deleteProfileImage : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }  
     }
     
     public function deactivateAccountAction( $header_data ){
         try{
             $user  = Users::findById( $header_data["id"] );
             $user->is_active   = 0;
             if( $user->save() ){
                Library::output(true, '0', USER_DEACTIVATED, null);
             }else{
                foreach ($user->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                Library::logging('error',"API : deactivateAccount : ".$errors." user_id : ".$header_data['id']);
                Library::output(false, '0', $errors, null);
             }
         } catch (Exception $e) {
            Library::logging('error',"API : deactivateAccount, error message : ".$e->getMessage(). ": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
         }
     }
  
     public function hideUserAction( $header_data, $post_data ){
        if( empty($post_data['user_id']) ) {
            Library::logging('alert',"API : hideUser : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
        try{
            $user  = Users::findById( $header_data["id"] );
            
            $query      = "return db.users.find( { '_id' : ObjectId('".$post_data['user_id']."'), 'is_active' : 1 } ).toArray()" ;
            $db         = Library::getMongo();
            $user_info  = $db->execute( $query );
            if($user_info['ok'] == 0) {
                Library::logging('error',"API : hideUser , mongodb error: ".$user_info['errmsg']." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            if( empty($user_info["retval"][0]) ){
               Library::logging('error',"API : hideUser : Invalid user to hide : user_id : ".$header_data['id']);
               Library::output(false, '0', "Invalid user to hide.", null);
            }
            if( empty($user->hidden_contacts) ){
               $user->hidden_contacts = array();
            }
            if( ! array_search($post_data['user_id'], $user->hidden_contacts ) ){
                $user->hidden_contacts[]    = $post_data['user_id'];
            }
            if( $user->save() ){
               Library::output(true, '0', USER_HIDDEN, null);
            }else{
               foreach ($user->getMessages() as $message) {
                   $errors[] = $message->getMessage();
               }
               Library::logging('error',"API : hideUser : ".$errors." user_id : ".$header_data['id']);
               Library::output(false, '0', $errors, null);
            }
        } catch (Exception $e) {
            Library::logging('error',"API : hideUser, error message : ".$e->getMessage(). ": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
  
     public function unhideUserAction( $header_data, $post_data ){
        if( empty($post_data['user_id']) ) {
            Library::logging('alert',"API : hideUser : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
        try{
            $user  = Users::findById( $header_data["id"] );
            if( empty($user->hidden_contacts) ){
               $user->hidden_contacts = array();
            }
            if( ($key = array_search($post_data['user_id'], $user->hidden_contacts )) ){
                unset( $user->hidden_contacts[$key] );
                $user->hidden_contacts  = array_values( $user->hidden_contacts );
            }
            if( $user->save() ){
               Library::output(true, '0', USER_HIDDEN, null);
            }else{
               foreach ($user->getMessages() as $message) {
                   $errors[] = $message->getMessage();
               }
               Library::logging('error',"API : hideUser : ".$errors." user_id : ".$header_data['id']);
               Library::output(false, '0', $errors, null);
            }
        } catch (Exception $e) {
            Library::logging('error',"API : hideUser, error message : ".$e->getMessage(). ": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
  
     public function getHiddenUsersAction( $header_data ){
        try{
            $user  = Users::findById( $header_data["id"] );
            if( empty($user->hidden_contacts) ){
               $user->hidden_contacts = array();
            }
            $result = array();
            $i      = 0;
            foreach($user->hidden_contacts AS $hiddenContact ){
                $query      = "return db.users.find( { '_id' : ObjectId('$hiddenContact'), 'is_active' : 1 } ).toArray()" ;
                $db         = Library::getMongo();
                $user_info  = $db->execute( $query );
                if($user_info['ok'] == 0) {
                    Library::logging('error',"API : getHiddenUsers , mongodb error: ".$user_info['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                if( isset($user_info["retval"][0]) ){
                    $contact                        = $user_info["retval"][0];
                    $result[$i]['user_id']          = $hiddenContact;
                    $result[$i]['username']         = isset($contact["username"]) ? $contact["username"] : '' ;
                    $result[$i]['profile_image']    = $contact["profile_image"];
                    $i++;
                }
            }
            Library::output(true, '0', "No error", $result);
        } catch (Exception $e) {
            Library::logging('error',"API : hideUser, error message : ".$e->getMessage(). ": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
     }
}
