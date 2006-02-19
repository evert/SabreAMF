<?php

    require_once(dirname(__FILE__) . '/OutputStream.php');
    require_once(dirname(__FILE__) . '/InputStream.php');
    require_once(dirname(__FILE__) . '/Message.php');
    require_once(dirname(__FILE__) . '/Const.php');


    /**
     * SabreAMF_Server 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
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

            $data = isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA']:file_get_contents(dirname(__FILE__) . '/test.amf');

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
         * @return array 
         */
        public function getRequests() {

            return $this->amfRequest->getBodies();

        }

        /**
         * setResponse 
         * 
         * @param string $target 
         * @param int $responsetype 
         * @param mixed $data 
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
         * @return void
         */
        public function sendResponse() {

            header('Content-Type: application/x-amf');
            $this->amfResponse->serialize($this->amfOutputStream);
            echo($this->amfOutputStream->getRawData());

        }

    }

?>
