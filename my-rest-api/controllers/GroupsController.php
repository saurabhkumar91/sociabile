<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class GroupsController 
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
    
    public function getGroupsAction($header_data)
    {
        try {
            $result = array();
            
            $db = Library::getMongo();
            $list = $db->execute('return db.groups.find( { $or: [ { is_active: 1 }, { user_id: "'.$header_data['id'].'" } ] } ).toArray();');
            
            if($list['ok'] == 0) {
                Library::logging('error',"API : sendRequest (request sent query) mongodb error: ".$list['errmsg']." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            $i = 0;
            foreach ($list['retval'] as $group) {
                $result[$i]['id'] = (string)$group['_id'];
                $result[$i]['group_name'] = $group['group_name'];
                $i++;
            }
            Library::output(true, '1', "No Error", $result);
        } catch (MongoException $e   ) {
            Library::logging('error',"API : getGroups, error message : ".$e->getMessage(). ": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
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
    
    public function addGroupAction($header_data,$post_data)
    {
        if( !isset($post_data['group_name'])) {
            Library::logging('alert',"API : addGroup : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $group = new Groups();
                $group->user_id = $header_data['id'];
                $group->group_name = $post_data['group_name'];
                 if ($group->save() == false) {
                    foreach ($group->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : addGroup : ".$errors." : user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                } else {
                    $result['group_id'] = (string)$group->_id;
                    Library::output(true, '1', GROUP_ADDED, $result);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : getGroups : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method for opening time capsule 
     * @param $header_data: array of header data
     * @param $post_data: array of post data(groupname) 
     * @author Saurabh Kumar
     * @return json
     */
    
    public function createChatGroupAction( $header_data, $post_data )
    {
        try{
                if(empty($post_data["groupname"]) || empty($post_data["members"]) ){
                    Library::logging('error',"API : createChatGroup : invalid parameters recieved : user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                if($header_data['os'] == 1) {
                    $post_data["members"] =  json_decode($post_data["members"]);
                }
                $members        = array( $header_data['id'] => array("member_id"=>$header_data['id'], "is_active"=>1) );
                $db             = Library::getMongo();
                $androidDevices = array();
                $iosDevices     = array();
                foreach($post_data["members"] AS $member){
                    if( $member == $header_data['id'] ){
                        continue;
                    }
                    $request    = 'return db.users.find( {"_id" : ObjectId("'.$member.'")}, {"device_token":1, "os":1} ).toArray()';
                    $result =  $db->execute($request);
                    if( empty($result['retval']) ) {
                        Library::logging('error',"API : createChatGroup : Invalid group member($member) : user_id : ".$header_data['id']);
                        Library::output(false, '0', "Invalid group member", null);
                    }
                    $members[ $member ]  = array(    "member_id" => $member, 
                                                    "is_active" => 0
                                            );
                    if( !empty($result['retval'][0]["os"]) && !empty($result['retval'][0]["device_token"]) ){
                        if( $result['retval'][0]["os"] == 1 ){
                            $androidDevices[] = $result['retval'][0]["device_token"];
                        }elseif( $result['retval'][0]["os"] == 2 ){
                            $iosDevices = $result['retval'][0]["device_token"];
                        }
                    }
                }
                if( count($members) < 2 ){
                        Library::logging('error',"API : createChatGroup : No member in group : user_id : ".$header_data['id']);
                        Library::output(false, '0', JAXL_NO_MUC_MEMBER, null);
                }
                $groupname  = $post_data["groupname"];
                $user       = Users::findById($header_data['id']);
                $chatGroup  = ChatGroups::find( array( array("group_name"=>$groupname) ) );
                if( $chatGroup ){
                    Library::logging('error',"API : createChatGroup : ".JAXL_MUC_EXISTS.": user_id : ".$header_data['id']);
                    Library::output(false, '0', JAXL_MUC_EXISTS, null);
                }
                require 'components/JAXL3/jaxl.php';
                $client = new JAXL(array(
                    'jid' => $user->jaxl_id,
                    'pass' => $user->jaxl_password,
                    'log_level' => JAXL_DEBUG
                ));
                $client->require_xep(array(
                        '0045',     // group chat
                        '0030'      // discover
                ));
                $chatGroupID    = $groupname."@conference.".JAXL_HOST_NAME;
                $roomFullJid    = new XMPPJid( $chatGroupID. "/" .$user->mobile_no );
                
                $client->add_cb('on_auth_success', function() {
                    $client         = $_SESSION["client"];
                    $chatGroupID    = $_SESSION["chatGroupID"];
                    $client->xeps['0030']->get_items($chatGroupID, function($stanza){
                        $chatGroupID    = $_SESSION["chatGroupID"];
                        $userId = $_SESSION["userId"];
                        if( isset($stanza->childrens[1]->childrens[0]->name) && strtolower(trim($stanza->childrens[1]->childrens[0]->name)) == "item-not-found" ){
                            $client     = $_SESSION["client"];
                            $roomJid    = $_SESSION["roomFullJid"];
                            $client->xeps['0045']->join_room($roomJid);
                        }else{
                            Library::logging('error',"API : createChatGroup : ".JAXL_MUC_EXISTS." : user_id : ".$userId." : chat_group_id : ".$chatGroupID);
                            Library::output(false, '0', JAXL_MUC_EXISTS, null);
                        }
                    });
                });
                $client->add_cb('on_auth_failure', function() {
                    $userId = $_SESSION["userId"];
                    Library::logging('error',"API : createChatGroup : ".JAXL_AUTH_FAILURE." : user_id : ".$userId);
                    Library::output(false, '0', JAXL_AUTH_FAILURE, null);
                });
                
                $client->add_cb('on_presence_stanza', function($stanza) {
                    $roomJid        = $_SESSION["roomFullJid"];
                    $chatGroupID    = $_SESSION["chatGroupID"];
                    $userId         = $_SESSION["userId"];
                    $groupName      = $_SESSION["groupName"];
                    $members        = $_SESSION["members"];
                    $androidDevices = $_SESSION["androidDevices"];
                    $iosDevices     = $_SESSION["iosDevices"];
                    $from = new XMPPJid($stanza->from);
                    // self-stanza received, we now have complete room roster
                    if( strtolower($from->to_string()) == strtolower( $roomJid->to_string() ) ) {
                        if(($x = $stanza->exists('x', NS_MUC.'#user')) !== false) {
                            if(($status = $x->exists('status', null, array('code'=>'110'))) !== false) {
                                //$item = $x->exists('item');
                                //exit("xmlns #user exists with x ".$x->ns." status ".$status->attrs['code'].", affiliation:".$item->attrs['affiliation'].", role:".$item->attrs['role']);\
                                $request = 'db.chat_groups.insert({ group_name: "'.$groupName.'", group_jid: "'.$chatGroupID.'", admin_id: "'.$userId.'", created_by: "'.$userId.'", members: '. json_encode($members) .'   })';

                                $db     = Library::getMongo();
                                $result = $db->execute($request);
                                if($result['ok'] == 0) {
                                    Library::logging('error',"API : createChatGroup, error_msg: ".$result['errmsg']." ".": user_id : ".$userId);
                                    Library::output(false, '0', JAXL_ERR_CREATE_MUC, null);
                                }
                                $settings   = new SettingsController();
                                $message    = array( "message"=>"You are added to group", "type"=>NOTIFY_JOIN_GROUP_CHAT, "group_name"=>$groupName, "group_jid"=>$chatGroupID, "admin_id"=>$userId, "members"=>$members );
                                if( $iosDevices ){
                                    $settings->sendNotifications( $iosDevices, array("message"=>json_encode($message)), "ios" );
                                }
                                if( $androidDevices ){
                                    $settings->sendNotifications( $androidDevices, array("message"=>json_encode($message)), "android" );
                                }
                                $roomJid    = $_SESSION["roomFullJid"];
                                $client     = $_SESSION["client"];
                                $fields     = array("var"=>"muc#roomconfig_persistentroom", "value"=>true);
                                $client->xeps['0045']->setRoomConfig($chatGroupID, $fields, function(){
                                    $chatGroupID    = $_SESSION["chatGroupID"];
                                    Library::output(true, '1', JAXL_MUC_CREATED, array("chatGroupId"=>$chatGroupID));
                                });
                            }
                            else {
                                $userId = $_SESSION["userId"];
                                Library::logging('error',"API : createChatGroup : ".JAXL_ERR_CREATE_MUC."(user have no x child element) : user_id : ".$userId);
                                Library::output(false, '0', JAXL_ERR_CREATE_MUC, null);
                            }
                        }
                        else {
                            Library::logging('error',"API : createChatGroup : ".JAXL_ERR_CREATE_MUC." : user_id : ".$userId);
                            Library::output(false, '0', JAXL_ERR_CREATE_MUC, null);
                        }
                    }
                });
                $client->add_cb('on_disconnect', function() {
                    $userId         = $_SESSION["userId"];
                    $chatGroupID    = $_SESSION["chatGroupID"];
                    Library::logging('error',"API : createChatGroup : ".JAXL_ERR_CREATE_MUC."(disconnected) : user_id : ".$userId." group_jid : ".$chatGroupID);
                    Library::output(false, '0', JAXL_ERR_CREATE_MUC, null);
                });
            
                $_SESSION["client"]         = $client;
                $_SESSION["roomFullJid"]    = $roomFullJid;
                $_SESSION["chatGroupID"]    = $chatGroupID;
                $_SESSION["userId"]         = $header_data['id'];
                $_SESSION["members"]        = array_values($members);
                $_SESSION["groupName"]      = $groupname;
                $_SESSION["androidDevices"] = $androidDevices;
                $_SESSION["iosDevices"]     = $iosDevices;
                
                $client->start();
                /******* code for subscribe(add) user end **************************************/
                    
                
        } catch(Exception $e) {
            Library::logging('error',"API : createChatGroup : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
    
    /**
     * Method for opening time capsule 
     * @param $header_data: array of header data
     * @param $post_data: array of post data(group_id) 
     * @author Saurabh Kumar
     * @return json
     */
    
    public function addMembersInChatGroupAction( $header_data, $post_data )
    {
        try{
            if(empty($post_data["group_id"]) || empty($post_data["members"]) ){
                Library::logging('error',"API : addMembersInChatGroup : invalid parameters recieved : user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            if($header_data['os'] == 1) {
                $post_data["members"] =  json_decode($post_data["members"]);
            }
            $group_id  = $post_data["group_id"];
            $chatGroup  = ChatGroups::findById( $group_id );
            if( !$chatGroup ){
                Library::logging('error',"API : createChatGroup : invalid parameters recieved(group name): user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            if( $chatGroup->admin_id != $header_data['id'] ){
                Library::logging('error',"API : createChatGroup : ".JAXL_MUC_ADD_MEMBERS_AUTH_ERROR.": user_id : ".$header_data['id']);
                Library::output(false, '0', JAXL_MUC_ADD_MEMBERS_AUTH_ERROR, null);
            }
            if( !is_array($post_data["members"]) || count($post_data["members"]) == 0 ){
                    Library::logging('error',"API : createChatGroup : No member to add in group : user_id : ".$header_data['id']);
                    Library::output(false, '0', JAXL_NO_MUC_MEMBER, null);
            }
            $members    = array();
            $db         = Library::getMongo();
            foreach($post_data["members"] AS $member){
                if( $member == $header_data['id'] ){
                    continue;
                }
                $memberAlreadyExists    = false;
                foreach( $chatGroup->members AS $value ){
                    if( $value["member_id"] == $member ){
                        $memberAlreadyExists    = true;
                        break;
                    }
                }
                if( $memberAlreadyExists ){
                    continue;
                }
                $request    = 'return db.users.find( {"_id" : ObjectId("'.$member.'")}, {"mobile_no":1} ).toArray()';
                $result =  $db->execute($request);
                if( empty($result['retval']) ) {
                    Library::logging('error',"API : addMembersInChatGroup : Invalid group member($member) : user_id : ".$header_data['id']);
                    Library::output(false, '0', "Invalid group member", null);
                }
                $members[$member]   = array("member_id"=>$member, "is_active"=>0);
            }
            $chatGroup->members = array_merge( array_values($members), $chatGroup->members );
            if ($chatGroup->save() == false) {
               foreach ($chatGroup->getMessages() as $message) {
                   $errors[] = $message->getMessage();
               }
               Library::logging('error',"API : createChatGroup : ".$errors." : user_id : ".$header_data['id']);
               Library::output(false, '0', ERROR_REQUEST, null);
            }
            Library::output(true, '1', JAXL_MUC_MEMBERS_ADDED, null);
        } catch(Exception $e) {
            Library::logging('error',"API : createChatGroup : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
    
    public function joinChatGroupAction( $header_data, $post_data )
    {
        try{
                if(empty($post_data["group_id"])){
                    Library::logging('error',"API : joinChatGroup : invalid parameters recieved(group id): user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $groupId    = $post_data["group_id"];
                $chatGroup  = ChatGroups::findById($groupId);
                if( !$chatGroup ){
                    Library::logging('error',"API : joinChatGroup : invalid parameters recieved(group name): user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $isMember   = false;
                foreach( $chatGroup->members AS $member ){
                    if( $member["member_id"] == $header_data['id'] ){
                        $isMember   = true;
                        break;
                    }
                }
                if( !$isMember ){
                    Library::logging('error',"API : joinChatGroup : ".JAXL_NOT_A_MUC_MEMBER." : user_id : ".$header_data['id']);
                    Library::output(false, '0', JAXL_NOT_A_MUC_MEMBER, null);
                }
                $user       = Users::findById($header_data['id']);
                require 'components/JAXL3/jaxl.php';
                $client = new JAXL(array(
                    'jid' => $user->jaxl_id,
                    'pass' => $user->jaxl_password,
                    'log_level' => JAXL_DEBUG
                ));
                $client->require_xep(array(
                        '0045',     // group chat
                        '0030'      // discover
                ));
                $chatGroupID    = $chatGroup->group_jid;
                $roomFullJid    = new XMPPJid( $chatGroupID. "/" .$user->mobile_no );
                
                $client->add_cb('on_auth_success', function() {
                    $client         = $_SESSION["client"];
                    $chatGroupID    = $_SESSION["chatGroupID"];
                    $client->xeps['0030']->get_items($chatGroupID, function($stanza){
                            $chatGroupID    = $_SESSION["chatGroupID"];
                            $userId = $_SESSION["userId"];
                            if( count($stanza->childrens) == 1 ){
                                $client     = $_SESSION["client"];
                                $roomJid    = $_SESSION["roomFullJid"];
                                $client->xeps['0045']->join_room($roomJid);
                            }else{
                                Library::logging('error',"API : joinChatGroup : ".JAXL_MUC_NOT_FOUND." : user_id : ".$userId." : chat_group_id : ".$chatGroupID);
                                Library::output(false, '0', JAXL_MUC_NOT_FOUND, null);
                            }
                    });
                });
                $client->add_cb('on_auth_failure', function() {
                    $userId = $_SESSION["userId"];
                    Library::logging('error',"API : joinChatGroup : ".JAXL_AUTH_FAILURE." : user_id : ".$userId);
                    Library::output(false, '0', JAXL_AUTH_FAILURE, null);
                });
                
                $client->add_cb('on_presence_stanza', function($stanza) {
                    $roomJid    = $_SESSION["roomFullJid"];
                    $userId     = $_SESSION["userId"];
                    $groupId    = $_SESSION["groupId"];
                    $from = new XMPPJid($stanza->from);
                    // self-stanza received, we now have complete room roster
                    if( strtolower($from->to_string()) == strtolower( $roomJid->to_string() ) ) {
                        if(($x = $stanza->exists('x', NS_MUC.'#user')) !== false) {
                            if(($status = $x->exists('status', null, array('code'=>'110'))) !== false) {
                                    $request = 'return db.chat_groups.update({"_id" :ObjectId("'.$groupId.'"), "members.member_id":"'.$userId.'" }, {$set:{"members.$.is_active":1}})';
                                    $db = Library::getMongo();
                                    $result =  $db->execute($request);
                                    if($result['ok'] == 0) {
                                        Library::logging('error',"API : joinChatGroup, error_msg: ".$result['errmsg']." ".": user_id : ".$userId);
                                        Library::output(false, '0', JAXL_ERR_JOIN_MUC, null);
                                    }
                                    
                                    Library::output(true, '1', JAXL_MUC_JOINED, null);
                            }
                            else {
                                $userId = $_SESSION["userId"];
                                Library::logging('error',"API : joinChatGroup : ".JAXL_ERR_JOIN_MUC."(user have no x child element) : user_id : ".$userId);
                                Library::output(false, '0', JAXL_ERR_JOIN_MUC, null);
                            }
                        }
                        else {
                            Library::logging('error',"API : joinChatGroup : ".JAXL_ERR_JOIN_MUC." : user_id : ".$userId);
                            Library::output(false, '0', JAXL_ERR_JOIN_MUC, null);
                        }
                    }
                });
            
                $_SESSION["client"]         = $client;
                $_SESSION["roomFullJid"]    = $roomFullJid;
                $_SESSION["chatGroupID"]    = $chatGroupID;
                $_SESSION["groupId"]        = $groupId;
                $_SESSION["userId"]         = $header_data['id'];
                $client->start();
                /******* code for subscribe(add) user end **************************************/
                    
                
        } catch(Exception $e) {
            Library::logging('error',"API : joinChatGroup : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
    
    public function leaveChatGroupAction( $header_data, $post_data )
    {
        try{
                if(empty($post_data["group_id"])){
                    Library::logging('error',"API : joinChatGroup : invalid parameters recieved(group id): user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $groupId    = $post_data["group_id"];
                $chatGroup  = ChatGroups::findById($groupId);
                if( !$chatGroup ){
                    Library::logging('error',"API : joinChatGroup : invalid parameters recieved(group id): user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $isMember   = false;
                foreach( $chatGroup->members AS $member ){
                    if( $member["member_id"] == $header_data['id'] ){
                        $isMember   = true;
                        break;
                    }
                }
                if( !$isMember ){
                    Library::logging('error',"API : joinChatGroup : ".JAXL_NOT_A_MUC_MEMBER." : user_id : ".$header_data['id']);
                    Library::output(false, '0', JAXL_NOT_A_MUC_MEMBER, null);
                }
                $user       = Users::findById($header_data['id']);
                require 'components/JAXL3/jaxl.php';
                $client = new JAXL(array(
                    'jid' => $user->jaxl_id,
                    'pass' => $user->jaxl_password,
                    'log_level' => JAXL_DEBUG
                ));
                $client->require_xep(array(
                        '0045'     // group chat
                ));
                $chatGroupID    = $chatGroup->group_jid;
                $roomFullJid    = new XMPPJid( $chatGroupID. "/" .$user->mobile_no );
                
                $client->add_cb('on_auth_success', function() {
                    $client         = $_SESSION["client"];
                    $roomJid    = $_SESSION["roomFullJid"];
                    $client->xeps['0045']->leave_room( $roomJid );
                });
                $client->add_cb('on_auth_failure', function() {
                    $userId = $_SESSION["userId"];
                    Library::logging('error',"API : joinChatGroup : ".JAXL_AUTH_FAILURE." : user_id : ".$userId);
                    Library::output(false, '0', JAXL_AUTH_FAILURE, null);
                });
                
                $client->add_cb('on_presence_stanza', function($stanza) {
                    $roomJid    = $_SESSION["roomFullJid"];
                    $userId     = $_SESSION["userId"];
                    $groupId    = $_SESSION["groupId"];
                    $from       = new XMPPJid($stanza->from);
                    // self-stanza received, we now have complete room roster
                    if( strtolower($from->to_string()) == strtolower( $roomJid->to_string() ) ) {
                        if(($x = $stanza->exists('x', NS_MUC.'#user')) !== false) {
                            if(($status = $x->exists('status', null, array('code'=>'110'))) !== false) {
                                    $request = 'return db.chat_groups.update({"_id" :ObjectId("'.$groupId.'"), "members.member_id":"'.$userId.'" }, {$set:{"members.$.is_active":1}})';
                                    $db = Library::getMongo();
                                    $result =  $db->execute($request);
                                    if($result['ok'] == 0) {
                                        Library::logging('error',"API : joinChatGroup, error_msg: ".$result['errmsg']." ".": user_id : ".$userId);
                                        Library::output(false, '0', JAXL_ERR_JOIN_MUC, null);
                                    }
                                    
                                    Library::output(true, '1', JAXL_MUC_JOINED, null);
                            }
                            else {
                                $userId = $_SESSION["userId"];
                                Library::logging('error',"API : joinChatGroup : ".JAXL_ERR_JOIN_MUC."(user have no x child element) : user_id : ".$userId);
                                Library::output(false, '0', JAXL_ERR_JOIN_MUC, null);
                            }
                        }
                        else {
                            Library::logging('error',"API : joinChatGroup : ".JAXL_ERR_JOIN_MUC." : user_id : ".$userId);
                            Library::output(false, '0', JAXL_ERR_JOIN_MUC, null);
                        }
                    }
                });
            
                $_SESSION["client"]         = $client;
                $_SESSION["roomFullJid"]    = $roomFullJid;
                $_SESSION["chatGroupID"]    = $chatGroupID;
                $_SESSION["groupId"]        = $groupId;
                $_SESSION["userId"]         = $header_data['id'];
                $client->start();
                /******* code for subscribe(add) user end **************************************/
                    
                
        } catch(Exception $e) {
            Library::logging('error',"API : joinChatGroup : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
    
    public function getChatGroupsAction( $header_data )
    {
        try{
            $request    = 'return db.chat_groups.find( {"members.member_id":"'.$header_data['id'].'" }).toArray()';
            $db         = Library::getMongo();
            $chatGroups = $db->execute($request);
            if( $chatGroups['ok'] == 0 ) {
                Library::logging('error',"API : getChatGroups, error_msg: ".$chatGroups['errmsg']." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            $result = array();
            foreach( $chatGroups["retval"] AS $chatGroup ){
                $members        = array();
                $membersCount   = 0;
                foreach( $chatGroup["members"] AS $member ){
                    $request    = 'return db.users.find( {"_id" : ObjectId("'.$member["member_id"].'") }, {mobile_no:1,username:1} ).toArray()';
                    $user       =  $db->execute($request);
                    if($user['ok'] == 0) {
                        Library::logging('error',"API : getChatGroups, error_msg: ".$user['errmsg']." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                    if( $user["retval"] ){
                        $members[$membersCount]["mobile_no"]    = $user["retval"][0]["mobile_no"];
                        $members[$membersCount]["username"]     = $user["retval"][0]["username"];
                        $members[$membersCount]["member_id"]    = $member["member_id"];
                        $membersCount++;
                    }
                }
                $result[]   = array(
                                    "id"            => (string)$chatGroup["_id"], 
                                    "group_name"    => $chatGroup["group_name"], 
                                    "group_jid"     => $chatGroup["group_jid"], 
                                    "admin_id"      => $chatGroup["admin_id"],
                                    "members"       => $members
                                );
            }
            Library::output(true, '1', "No Error", $result);
        } catch(Exception $e) {
            Library::logging('error',"API : getChatGroups : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
}
?>
