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
    
    public function createChatGroupAction( $header_data, $groupname )
    {
        try{
                if(empty($groupname)){
                    Library::logging('error',"API : createChatGroup : invalid parameters recieved(group name): user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $user   = Users::findById($header_data['id']);
                require 'JAXL-3.x/jaxl.php';
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
                    $from = new XMPPJid($stanza->from);
                    // self-stanza received, we now have complete room roster
                    if( strtolower($from->to_string()) == strtolower( $roomJid->to_string() ) ) {
                        if(($x = $stanza->exists('x', NS_MUC.'#user')) !== false) {
                            if(($status = $x->exists('status', null, array('code'=>'110'))) !== false) {
                                    //$item = $x->exists('item');
                                    //exit("xmlns #user exists with x ".$x->ns." status ".$status->attrs['code'].", affiliation:".$item->attrs['affiliation'].", role:".$item->attrs['role']);
                                
                                    $request = 'db.chat_groups.insert({ 
                                            group_name: "'.$groupName.'", 
                                            group_jid: "'.$chatGroupID.'",
                                            admin_id: "'.$userId.'",
                                            created_by: "'.$userId.'",
                                            members: "['.$userId.']",
                                    })';

                                    $db = Library::getMongo();
                                    $result =  $db->execute($request);
                                    if($result['ok'] == 0) {
                                        Library::logging('error',"API : createChatGroup, error_msg: ".$result['errmsg']." ".": user_id : ".$userId);
                                    }
                                    
                                    Library::output(true, '1', JAXL_MUC_CREATED, array("chatGroupId"=>$chatGroupID));
                                    
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
            
                $_SESSION["client"]         = $client;
                $_SESSION["roomFullJid"]    = $roomFullJid;
                $_SESSION["chatGroupID"]    = $chatGroupID;
                $_SESSION["userId"]         = $header_data['id'];
                $_SESSION["groupName"]      = $groupname;
                $client->start();
                /******* code for subscribe(add) user end **************************************/
                    
                
        } catch(Exception $e) {
            Library::logging('error',"API : createChatGroup : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
    
    public function joinChatGroupAction( $header_data, $group )
    {
        try{
                if(empty($group["id"])){
                    Library::logging('error',"API : createChatGroup : invalid parameters recieved(group name): user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $user   = Users::findById($header_data['id']);
                require 'JAXL-3.x/jaxl.php';
                $client = new JAXL(array(
                    'jid' => $user->jaxl_id,
                    'pass' => $user->jaxl_password,
                    'log_level' => JAXL_DEBUG
                ));
                $client->require_xep(array(
                        '0045',     // group chat
                        '0030'      // discover
                ));
                
                $chatGroup      = ChatGroups::findById($group["id"]);
                if( !$chatGroup ){
                    Library::logging('error',"API : createChatGroup : invalid parameters recieved(group name): user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
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
                                    //$item = $x->exists('item');
                                    //exit("xmlns #user exists with x ".$x->ns." status ".$status->attrs['code'].", affiliation:".$item->attrs['affiliation'].", role:".$item->attrs['role']);
                                
                                    $request = 'db.chat_groups.update({"_id" :ObjectId("'.$groupId.'") }, {$push : {members:'.$userId.'})';
                                    $db = Library::getMongo();
                                    $result =  $db->execute($request);
                                    if($result['ok'] == 0) {
                                        Library::logging('error',"API : joinChatGroup, error_msg: ".$result['errmsg']." ".": user_id : ".$userId);
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
                $_SESSION["groupId"]        = $group["id"];
                $_SESSION["userId"]         = $header_data['id'];
                $client->start();
                /******* code for subscribe(add) user end **************************************/
                    
                
        } catch(Exception $e) {
            Library::logging('error',"API : joinChatGroup : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
}
?>
