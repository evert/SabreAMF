<?php

    require_once(dirname(dirname(__FILE__)) . '/core/SabreRPCResponse.php');
    require_once(dirname(__FILE__) . '/Serializer.php');

    class SabreAMFResponse extends SabreRPCResponse  {

        private $amfResults = array();
        private $responseVersion = 0;
        private $amfHeaders = array();

        function addResult($amfresult) {

            $this->amfResults[] = $amfresult;

        }

        function serialize() {

            $serializer = new SabreAMFSerializer();
            
            $serializer->writeByte($this->responseVersion);
            $serializer->writeByte(0);
            //$serializer->writeLong(count($this->amfHeaders));

            foreach($this->amfHeaders as $header) {

                $this->writeHeader($header);

            }

            $serializer->writeLong(count($this->amfResults));

            foreach($this->amfResults as $result) {

                //print_r($result);
                //$this->pagesize = $body->getPageSize(); // save pagesize
                $serializer->writeString($result['responseURI']);
                $serializer->writeString("null");
                $serializer->writeLong(-1);
                $serializer->writeData($result['body'],(isset($result['type'])?$result['type']:-1));
            }

            return $serializer->getRawData();
 
        }

        function unserialize($rawdata) {

        }

    }

?>
