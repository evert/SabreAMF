<?php

    require_once dirname(__FILE__) . '/Deserializer.php'; 
    require_once dirname(__FILE__) . '/Serializer.php'; 

    class SabreAMF_Message {

        private $inputStream;
        private $outputStream;
        private $clientType=0;
        private $bodies=array();
        private $headers=array();

        public function serialize(SabreAMF_OutputStream $stream) {

            $this->outputStream = $stream;
            $stream->writeByte(0x00);
            $stream->writeByte($this->clientType);
            $stream->writeInt(count($this->headers));
            
            foreach($this->headers as $header) {

                $serializer = new SabreAMF_Serializer($stream);
                
                $stream->writeString($header['name']);
                $stream->writeByte($header['required']);
                $stream->writeLong(-1);
                $seralizer->writeAMFData($header['data']);
            }
            $stream->writeInt(count($this->bodies));

            foreach($this->bodies as $body) {
                $serializer = new SabreAMF_Serializer($stream);
                $stream->writeString($body['target']);
                $stream->writeString("null");
                $stream->writeLong(-1);
                $serializer->writeAMFData($body['data']);
            }

        }

        public function deserialize(SabreAMF_InputStream $stream) {

            $this->InputStream = $stream;
          
            $this->clientType = $stream->readByte();

            $totalHeaders = $stream->readInt();

            for($i=0;$i<$totalHeaders;$i++) {

                $deserializer = new SabreAMF_Deserializer($stream);
                $header = array(
                    'name'     => $stream->readString(),
                    'required' => $stream->readByte()==true
                );
                $stream->readLong();
                $header['data']  = $deserializer->readAMFData();
                $this->headers[] = $header;    

            }

    
            $totalBodies = $stream->readInt();

            for($i=0;$i<$totalBodies;$i++) {

                $deserializer = new SabreAMF_Deserializer($stream);

                $body = array(
                    'target'   => $stream->readString(),
                    'response' => $stream->readString(),
                    'length'   => $stream->readLong(),
                    'data'     => $deserializer->readAMFData()
                );  
                $this->bodies[] = $body;    

            }


        }

        public function getClientType() {

            return $this->clientType;

        }

        public function getBodies() {

            return $this->bodies;

        }

        public function addBody($body) {

            $this->bodies[] = $body;

        }

    }

?>
