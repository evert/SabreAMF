<?php

    require_once(dirname(__FILE__) . '/OutputStream.php');
    require_once(dirname(__FILE__) . '/InputStream.php');
    require_once(dirname(__FILE__) . '/Message.php');
    require_once(dirname(__FILE__) . '/Const.php');


    class SabreAMF_Server {

        private $amfInputStream;
        private $amfOutputStream;
        private $amfRequest;
        private $amfResponse;

        public function __construct($dump = false) {

            $data = isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA']:file_get_contents(dirname(__FILE__) . '/test.amf');

            if ($dump) file_put_contents($dump.'/' . md5($data),$data);

            $this->amfInputStream = new SabreAMF_InputStream($data);

           
            $this->amfRequest = new SabreAMF_Message();
            $this->amfOutputStream = new SabreAMF_OutputStream();
            $this->amfResponse = new SabreAMF_Message();
            
            $this->amfRequest->deserialize($this->amfInputStream);

        }

        public function getRequests() {

            return $this->amfRequest->getBodies();

        }

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

        public function sendResponse() {

            header('Content-Type: application/x-amf');
            echo($this->amfResponse->serialize($this->amfOutputStream));
            echo($this->amfOutputStream->getRawData());

        }

    }

?>
