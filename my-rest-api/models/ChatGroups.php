<?php

use Phalcon\Mvc\Model,
    Phalcon\Mvc\Model\Message,
    Phalcon\Mvc\Model\Validator\InclusionIn,
    Phalcon\Mvc\Model\Validator\Uniqueness;

class ChatGroups extends \Phalcon\Mvc\Collection
{
    public function getSource()
    {
        return "chat_groups";
    }
    
}
