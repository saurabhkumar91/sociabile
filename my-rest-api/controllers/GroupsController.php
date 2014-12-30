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
    
}
?>
