<?php

require_once JAXL_CWD.'/xmpp/xmpp_xep.php';

define('NS_CMD', 'http://jabber.org/protocol/commands');

class XEP_0133 extends XMPPXep {
	
	//
	// abstract method
	//
	
	public function init() {
		return array();
	}
	
        
        public function deleteUser( $domain, $callback=false) {
            $query = new JAXLXml(   'command', 
                                    NS_CMD, 
                                    array("action"=>'execute', "node"=>'http://jabber.org/protocol/admin#delete-user') 
                    );
            $pkt        = $this->jaxl->get_iq_pkt(
                                array('type'=>'set', 'from'=>$this->jaxl->full_jid->to_string(), 'to'=>$domain),
                                $query	
                        );
            if($callback){
                $this->jaxl->add_cb('on_stanza_id_'.$pkt->id, $callback);
            }
            $this->jaxl->send($pkt);
        }

}

?>
