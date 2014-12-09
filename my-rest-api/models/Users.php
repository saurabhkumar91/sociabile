<?php

use Phalcon\Mvc\Model,
    Phalcon\Mvc\Model\Message,
    Phalcon\Mvc\Model\Validator\InclusionIn,
    Phalcon\Mvc\Model\Validator\Uniqueness;


class Users extends \Phalcon\Mvc\Collection
{
    public function getSource()
    {
        return "users";
    }
    
//    public function validation() {
//        $this->validate(new Uniqueness(
//            array(
//                "mobile_no"   => "name",
//                "message" => "The mobile number must be unique"
//            )
//        ));
//    }
}
