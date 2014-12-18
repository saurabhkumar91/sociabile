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
            $groups = Groups:: find(array(array("is_active"=>1)));
            $i = 0;
            foreach ($groups as $group) {
                $result[$i]['id'] = (string)$group->_id;
                $result[$i]['group_name'] = $group->group_name;
                $i++;
            }
            Library::output(true, '1', "No Error", $result);
        } catch (Exception $e) {
            Library::logging('error',"API : getGroups : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
}
?>
