<?php

    require_once(dirname(__FILE__) . '/Message.php');
    require_once(dirname(__FILE__) . '/OutputStream.php');
    require_once(dirname(__FILE__) . '/InputStream.php');
 
    class SabreAMF_Client {

        private $endPoint; 
        private $amfInputStream;
        private $amfOutputStream;
        private $amfRequest;
        private $amfResponse;

        public function __construct($endPoint) {

            $this->endPoint = $endPoint;

            $this->amfRequest = new SabreAMF_Message();
            $this->amfOutputStream = new SabreAMF_OutputStream();

        }


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
