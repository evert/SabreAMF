<?php

    require_once dirname(__FILE__) . '/AMF0/Serializer.php'; 
    require_once dirname(__FILE__) . '/AMF0/Deserializer.php'; 


    /**
     * SabreAMF_Message 
     * 
     * The Message class encapsulates either an entire request package or an entire result package; including an AMF enveloppe
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
         * This method serializes a request. It requires an SabreAMF_OutputStream as an argument to read
         * the AMF Data from. After serialization the Outputstream will contain the encoded AMF data.
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
                
                $serializer->writeString($header['name']);
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
         * This method deserializes a request. It requires an SabreAMF_InputStream with valid AMF data. After
         * deserialization the contents of the request can be found through the getBodies and getHeaders methods
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
                $header['data']  = $deserializer->readAMFData(null,true);
                $this->headers[] = $header;    

            }
 
            $totalBodies = $stream->readInt();

            for($i=0;$i<$totalBodies;$i++) {

                try {
                    $target = $deserializer->readString();
                } catch (Exception $e) {
                    // Could not fetch next body.. this happens with some versions of AMFPHP where the body
                    // count isn't properly set. If this happens we simply stop decoding
                    break;
                }

                $body = array(
                    'target'   => $target,
                    'response' => $deserializer->readString(),
                    'length'   => $stream->readLong(),
                    'data'     => $deserializer->readAMFData(null,true)
                );
                
                $this->bodies[] = $body;    

            }


        }

        /**
         * getClientType 
         * 
         * Returns the ClientType for the request. Check SabreAMF_Const for possible (known) values
         * 
         * @return int 
         */
        public function getClientType() {

            return $this->clientType;

        }

        /**
         * getBodies 
         * 
         * Returns the bodies int the message
         * 
         * @return array 
         */
        public function getBodies() {

            return $this->bodies;

        }

        /**
         * getHeaders 
         * 
         * Returns the headers in the message
         * 
         * @return array 
         */
        public function getHeaders() {

            return $this->headers;

        }

        /**
         * addBody 
         *
         * Adds a body to the message
         * 
         * @param mixed $body 
         * @return void 
         */
        public function addBody($body) {

            $this->bodies[] = $body;

        }

        /**
         * addHeader 
         * 
         * Adds a message header
         * 
         * @param mixed $header 
         * @return void
         */
        public function addHeader($header) {

            $this->headers[] = $header;

        }

    }

?>