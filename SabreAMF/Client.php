<?php

    require_once(dirname(__FILE__) . '/Message.php');
    require_once(dirname(__FILE__) . '/OutputStream.php');
    require_once(dirname(__FILE__) . '/InputStream.php');
 
    /**
     * AMF Client
     *
     * Use this class to make a call to an AMF0/AMF3 Server 
     * 
     * @package SabreAMF
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License
     * @link http://www.osflash.org/sabreamf
     * @example ../examples/client.php
     */
    class SabreAMF_Client {

        /**
         * endPoint 
         * 
         * @var mixed
         */
        private $endPoint; 
        /**
         * amfInputStream 
         * 
         * @var mixed
         */
        private $amfInputStream;
        /**
         * amfOutputStream 
         * 
         * @var mixed
         */
        private $amfOutputStream;
        /**
         * amfRequest 
         * 
         * @var mixed
         */
        private $amfRequest;
        /**
         * amfResponse 
         * 
         * @var mixed
         */
        private $amfResponse;

        /**
         * __construct 
         * 
         * @param mixed $endPoint The url to the AMF gateway
         * @return void
         */
        public function __construct($endPoint) {

            $this->endPoint = $endPoint;

            $this->amfRequest = new SabreAMF_Message();
            $this->amfOutputStream = new SabreAMF_OutputStream();

        }


        /**
         * sendRequest 
         *
         * sendRequest sends the request to the server. It expects the servicepath and methodname, and the parameters of the methodcall
         * 
         * @param mixed $servicePath The servicepath (e.g.: myservice.mymethod)
         * @param mixed $data The parameters you want to send
         * @return mixed 
         */
        function sendRequest($servicePath,$data) {

            $ch = curl_init($this->endPoint);
            $this->amfRequest->addBody(array(
                'target'   => $servicePath,
                'response' => '/1',
                'data'     => $data
            ));
            $this->amfRequest->serialize($this->amfOutputStream);

            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_TIMEOUT,20);
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-amf'));
            curl_setopt($ch,CURLOPT_POSTFIELDS,$this->amfOutputStream->getRawData());

            $result = curl_exec($ch);
 
            if (curl_errno($ch)) {
                throw new Exception('CURL error: ' . curl_error($ch));
                false;
            } else {
                curl_close($ch);
            }
        
            $this->amfInputStream = new SabreAMF_InputStream($result);
            $this->amfResponse = new SabreAMF_Message(); 
            $this->amfResponse->deserialize($this->amfInputStream);

            foreach($this->amfResponse->getBodies() as $body) {

                if (strpos($body['target'],'/1')===0) return $body['data'] ;

            }

        }


    }


?>
