<?php
function wait_for_register_response($event, $args) {
	$client = $_SESSION["client"];
	$form = $_SESSION["form"];
	
	if($event == 'stanza_cb') {
		$stanza = $args[0];
		if($stanza->name == 'iq') {
			$form['type'] = $stanza->attrs['type'];
			if($stanza->attrs['type'] == 'result') {
				$client->send_end_stream();
                                $_SESSION["form"]   = $form;
				return "logged_out";
			}
			else if($stanza->attrs['type'] == 'error') {
				$error = $stanza->exists('error');
                                $form['type'] = "registration failed with error code: ".$error->attrs['code']." and type: ".$error->attrs['type'].PHP_EOL;
				$client->send_end_stream();
                                $_SESSION["form"]   = $form;
				return "logged_out";
			}
		}
	}
	else {
                $form['type']  = "unhandled event $event rcvd";
                $_SESSION["form"]   = $form;
	}
}

function wait_for_register_form($event, $args) {
	$client = $_SESSION["client"];
	$form = $_SESSION["form"];
	
	$stanza = $args[0];
	$query = $stanza->exists('query', NS_INBAND_REGISTER);
	if($query) {
//		$instructions = $query->exists('instructions');
//		if($instructions) {
//			echo $instructions->text.PHP_EOL;
//		}
		$client->xeps['0077']->set_form($stanza->attrs['from'], $form);
                $_SESSION["client"]  = $client;
                $_SESSION["form"]  = $form;
		return "wait_for_register_response";
	}
	else {
		$client->end_stream();
		return "logged_out";
	}
}
