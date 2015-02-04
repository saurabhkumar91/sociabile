<?php

/**
 * Description of RegisterUser
 *
 * @author user
 */

function wait_for_register_response($event, $args) {
	global $client, $form;
	
	if($event == 'stanza_cb') {
		$stanza = $args[0];
		if($stanza->name == 'iq') {
			$form['type'] = $stanza->attrs['type'];
			if($stanza->attrs['type'] == 'result') {
				echo "registration successful".PHP_EOL."shutting down...".PHP_EOL;
				$client->send_end_stream();
				return "logged_out";
			}
			else if($stanza->attrs['type'] == 'error') {
				$error = $stanza->exists('error');
				echo "registration failed with error code: ".$error->attrs['code']." and type: ".$error->attrs['type'].PHP_EOL;
				echo "error text: ".$error->exists('text')->text.PHP_EOL;
				echo "shutting down...".PHP_EOL;
				$client->send_end_stream();
				return "logged_out";
			}
		}
	}
	else {
		_notice("unhandled event $event rcvd");
	}
}

function wait_for_register_form($event, $args) {
    exit("test3");

	global $client, $form;
	
	$stanza = $args[0];
	$query = $stanza->exists('query', NS_INBAND_REGISTER);
	if($query) {
		$instructions = $query->exists('instructions');
		if($instructions) {
			echo $instructions->text.PHP_EOL;
		}
                //$form=array( "username"=>"123", "password"=>"123" );

		
//		foreach($query->childrens as $k=>$child) {
//			if($child->name != 'instructions') {
//				$form[$child->name] = readline($child->name.":");
//				
//			}
//		}
		
		$client->xeps['0077']->set_form($stanza->attrs['from'], $form);
		return "wait_for_register_response";
	}
	else {
		$client->end_stream();
		return "logged_out";
	}
}
