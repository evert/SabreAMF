<?php

    require_once dirname(__FILE__) . '/AMF0/Serializer.php'; 
    require_once dirname(__FILE__) . '/AMF0/Deserializer.php'; 


    /**
     * SabreAMF_Message 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    class SabreAMF_Message {

        /**
         * inputStream 
         * 
         * @var SabreAMF_InputStream 
         */
        private $inputStream;
        /**
         * outputStream 
         * 
         * @var SabreAMF_OutputStream 
         */
        private $outputStream;
        /**
         * clientType 
         * 
         * @var int 
         */
        private $clientType=0;
        /**
         * bodies 
         * 
         * @var array
         */
        private $bodies=array();
        /**
         * headers 
         * 
         * @var array
         */
        private $headers=array();

        /**
         * serialize 
         * 
         * @param SabreAMF_OutputStream $stream 
         * @return void
         */
        public function serialize(SabreAMF_OutputStream $stream) {

            $this->outputStream = $stream;
            $stream->writeByte(0x00);
            $stream->writeByte($this->clientType);
            $stream->writeInt(count($this->headers));
            
            foreach($this->headers as $header) {

                $serializer = new SabreAMF_AMF0_Serializer($stream);
                
                $stream->writeString($header['name']);
                $stream->writeByte($header['required']==true);
                $stream->writeLong(-1);
                $serializer->writeAMFData($header['data']);
            }
            $stream->writeInt(count($this->bodies));

            foreach($this->bodies as $body) {
                $serializer = new SabreAMF_AMF0_Serializer($stream);
                $serializer->writeString($body['target']);
                $serializer->writeString($body['response']);
                $stream->writeLong(-1);
                $serializer->writeAMFData($body['data']);
            }

        }

        /**
         * deserialize 
         * 
         * @param SabreAMF_InputStream $stream 
         * @return void
         */
        public function deserialize(SabreAMF_InputStream $stream) {

            $this->headers = array();
            $this->bodies = array();

            $this->InputStream = $stream;

            $stream->readByte();
          
            $this->clientType = $stream->readByte();

            $deserializer = new SabreAMF_AMF0_Deserializer($stream);

            $totalHeaders = $stream->readInt();

            for($i=0;$i<$totalHeaders;$i++) {

                $header = array(
                    'name'     => $deserializer->readString(),
                    'required' => $stream->readByte()==true
                );
                $stream->readLong();
                $header['data']  = $deserializer->readAMFData();
                $this->headers[] = $header;    

            }
 
            $totalBodies = $stream->readInt();

            for($i=0;$i<$totalBodies;$i++) {


                $body = array(
                    'target'   => $deserializer->readString(),
                    'response' => $deserializer->readString(),
                    'length'   => $stream->readLong(),
                    'data'     => $deserializer->readAMFData()
                );
                
                $this->bodies[] = $body;    

            }


        }

        /**
         * getClientType 
         * 
         * @return int 
         */
        public function getClientType() {

            return $this->clientType;

        }

        /**
         * getBodies 
         * 
         * @return array 
         */
        public function getBodies() {

            return $this->bodies;

        }

        /**
         * getHeaders 
         * 
         * @return array 
         */
        public function getHeaders() {

            return $this->headers;

        }

        /**
         * addBody 
         * 
         * @param mixed $body 
         * @return void 
         */
        public function addBody($body) {

            $this->bodies[] = $body;

        }

    }

?>
