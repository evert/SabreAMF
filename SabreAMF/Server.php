<?php

    require_once(dirname(__FILE__) . '/OutputStream.php');
    require_once(dirname(__FILE__) . '/InputStream.php');
    require_once(dirname(__FILE__) . '/Message.php');
    require_once(dirname(__FILE__) . '/Const.php');


    /**
     * AMF Server
     * 
     * This is the AMF0/AMF3 Server class. Use this class to construct a gateway for clients to connect to 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     * @uses SabreAMF_OutputStream
     * @uses SabreAMF_InputStream
     * @uses SabreAMF_Message
     * @uses SabreAMF_Const
     * @example ../examples/server.php
     */
    class SabreAMF_Server {

        /**
         * amfInputStream 
         * 
         * @var SabreAMF_InputStream 
         */
        private $amfInputStream;
        /**
         * amfOutputStream 
         * 
         * @var SabreAMF_OutputStream 
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
         * @param bool $dump 
         * @return void
         */
        public function __construct($dump = false) {

            $data = isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA']:false;

            if (!$data) throw new Exception('No valid AMF request received');

            if ($dump) file_put_contents($dump.'/' . md5($data),$data);

            $this->amfInputStream = new SabreAMF_InputStream($data);
           
            $this->amfRequest = new SabreAMF_Message();
            $this->amfOutputStream = new SabreAMF_OutputStream();
            $this->amfResponse = new SabreAMF_Message();
            
            $this->amfRequest->deserialize($this->amfInputStream);

        }

        /**
         * getRequests 
         * 
         * Returns the requests that are made to the gateway.
         * 
         * @return array 
         */
        public function getRequests() {

            return $this->amfRequest->getBodies();

        }

        /**
         * setResponse 
         * 
         * Send a response back to the client (based on a request you got through getRequests)
         * 
         * @param string $target This parameter should contain the same as the 'response' item you got through getRequests. This connects the request to the response
         * @param int $responsetype Set as either SabreAMF_Const::R_RESULT or SabreAMF_Const::R_STATUS, depending on if the call succeeded or an error was produced
         * @param mixed $data The result data
         * @return void
         */
        public function setResponse($target,$responsetype,$data) {


            switch($responsetype) {

                 case SabreAMF_Const::R_RESULT :
                        $target = $target.='/onResult';
                        break;
                 case SabreAMF_Const::R_STATUS :
                        $target = $target.='/onStatus';
                        break;
                 case SabreAMF_Const::R_DEBUG :
                        $target = '/onDebugEvents';
                        break;
            }
            return $this->amfResponse->addBody(array('target'=>$target,'response'=>'null','data'=>$data));

        }

        /**
         * sendResponse 
         *
         * Sends the responses back to the client. Call this after you answered all the requests with setResponse
         * 
         * @return void
         */
        public function sendResponse() {

            header('Content-Type: application/x-amf');
            $this->amfResponse->setEncoding($this->amfRequest->getEncoding());
            $this->amfResponse->serialize($this->amfOutputStream);
            echo($this->amfOutputStream->getRawData());

        }

        /**
         * addHeader 
         *
         * Add a header to the server response
         * 
         * @param string $name 
         * @param bool $required 
         * @param mixed $data 
         * @return void
         */
        public function addHeader($name,$required,$data) {

            $this->amfResponse->addHeader(array('name'=>$name,'required'=>$required==true,'data'=>$data));

        }

        /**
         * getRequestHeaders
         *
         * returns the request headers
         *
         * @return void
         */
        public function getRequestHeaders() {
            
            return $this->amfRequest->getHeaders();

        }

    }

?>
