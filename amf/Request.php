<?php

    require_once(dirname(dirname(__FILE__)) . '/core/SabreRPCRequest.php');
    require_once(dirname(__FILE__) . '/Deserializer.php');

    define('AMF_APP_FLASH',0);
    define('AMF_APP_FLASHCOM',1);

    class SabreAMFRequest extends SabreRPCRequest {

        private $requestVersion = 0;
        private $requestApplication = AMF_APP_FLASH;
        private $amfHeaders = array();
        private $amfCalls = array();
    
        function getCalls() {

            return $this->amfCalls;

        }
    
        function getHeaders() {

            return $this->amfHeaders;

        }

        function serialize() {

            $serializer = new SabreAMFSerializer();
            
            $serializer->writeByte($this->requestVersion);
            $serializer->writeByte($this->requestApplication);

            return $serializer->getRawData();
        }

        function unserialize($rawData) {

            $deserializer = new SabreAMFDeserializer($rawData);

            $this->requestVersion = $deserializer->readByte();
            $this->requestApplication = $deserializer->readByte();

            $headerCount = $deserializer->readInt();
        
            for($currHeader = 0;$currHeader<$headerCount;$currHeader++) {

                $amfHeader = array(
                    'name' => $deserializer->readString(),
                    'mustUnderstand' => $deserializer->readByte()==1,
                );

                $headerLength = $deserializer->readLong(); 
                $varType = $deserializer->readByte();

                $amfHeader['content'] = $deserializer->readData($varType);
                $this->amfHeaders[] = $amfHeader;

            }


            $bodyCount = $deserializer->readInt();

            for($currBody = 0;$currBody<$bodyCount;$currBody++) {

                $amfBody = array(
                    'target'   => $deserializer->readString(),
                    'response' => $deserializer->readString(),
                );
                $bodyLengh = $deserializer->readLong();
                $varType = $deserializer->readByte();
                $amfBody['content'] = $deserializer->readData($varType);
                $this->amfCalls[] = $amfBody;
            }


        }

    }

?>
