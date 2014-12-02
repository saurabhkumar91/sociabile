<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;

class RobotsController extends ControllerBase
{

    /**
     * Method for add new event
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
	
    public function indexAction()
    {
    	//$robot = Robots::find();
        //print_r($robot[0]);    
        //echo $robot->robotName;
        
//        $robot = Robots::findFirst(array(
//                array('robotName' => 'alok')
//        ));
//        $robot->robotName = "sachin ";
//        $robot->save();
//        echo "value change successfully";
        
         
        $robot       = new Robots();
        $robot->type = "mechanical";
        $robot->name = "Astro Boy f";
        $robot->year = 1952;
        if ($robot->save() == false) {
            echo "Umh, We can't store robots right now: \n";
            foreach ($robot->getMessages() as $message) {
                echo $message, "\n";
            }
        } else {
            echo "Great, a new robot was saved successfully!";
        }
    }

    

}
