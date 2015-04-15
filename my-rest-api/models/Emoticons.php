<?php

use Phalcon\Mvc\Model,
    Phalcon\Mvc\Model\Message,
    Phalcon\Mvc\Model\Validator\InclusionIn,
    Phalcon\Mvc\Model\Validator\Uniqueness;

/**
 * Description of TimeCapsules
 * @author user
 */
class Emoticons extends \Phalcon\Mvc\Collection{
    
    public function getSource()
    {
        return "emoticons";
    }
    
}
