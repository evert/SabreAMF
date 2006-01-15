<?php

    require_once(dirname(dirname(__FILE__)) . '/core/SabreRPCServer.php');
    require_once(dirname(__FILE__) . '/Request.php');
    require_once(dirname(__FILE__) . '/Response.php');


    class SabreAMFServer extends SabreRPCServer {

        public function __construct() {

            $this->request = new SabreAMFRequest();
            $this->response = new SabreAMFResponse();

        }

        public function parseRequest() {

            $this->request->unserialize($GLOBALS["HTTP_RAW_POST_DATA"]);
            $this->parseHeaders();
        }

        public function parseHeaders() {

            foreach($this->request->getHeaders() as $header) {

                switch($header['name']) {

                    case 'Credentials' :
                        $this->setCredentials($header['content']['userid'],$header['content']['password']);
                        break;
                    case 'amf_server_debug' :
                        $options = $header['content'];
                        $response = array();
                        $response['responseURI'] = '/onDebugEvents';

                        if ($options['httpheaders']) {
                            die('HTTP headers debug not implemented yet');
                        }
                        if ($options['amfheaders']) {
                            die('AMF headers debug not implemented yet');
                        }
                        if ($options['trace']) {
                            //die('Trace debug not implemented yet');
                        }
                        if ($options['amf']) {
                            die('AMF data debug not implemented yet');
                        }
                        $response['body'] = false;
                        //$this->response->addResult($response);
                        break;
                    default : print_r($header);
                }

           } 

        }
        

        public function sendResponse() {

            echo($this->getResponse()->serialize());

        }


    }

?>
