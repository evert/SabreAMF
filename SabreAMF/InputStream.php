<?php

    /**
     * SabreAMF_InputStream 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    class SabreAMF_InputStream {

        /**
         * cursor 
         * 
         * @var int 
         */
        private $cursor = 0;
        /**
         * rawData 
         * 
         * @var string
         */
        private $rawData = '';


        /**
         * __construct 
         * 
         * @param string $data 
         * @return void
         */
        public function __construct($data) {

            //Rawdata has to be a string
            if (!is_string($data)) {
                throw new Exception('Inputdata is not of type String');
                return false;
            }
            $this->rawData = $data;

        }

        /**
         * &readBuffer 
         * 
         * @param int $length 
         * @return mixed 
         */
        public function &readBuffer($length) {

            if ($length+$this->cursor > strlen($this->rawData)) {
                throw new Exception('Buffer underrun at position: '. $this->cursor . '. Trying to fetch '. $length . ' bytes');
                return false;
            }
            $data = substr($this->rawData,$this->cursor,$length);
            $this->cursor+=$length;
            return $data;

        }

        /**
         * readByte 
         * 
         * @return int 
         */
        public function readByte() {

            return ord($this->readBuffer(1));

        }

        /**
         * readInt 
         * 
         * @return int 
         */
        public function readInt() {

            $block = $this->readBuffer(2);
            $int = unpack("n",$block);
            return $int[1];

        }


        /**
         * readDouble 
         * 
         * @return float 
         */
        public function readDouble() {

            $double = $this->readBuffer(8);

            $testEndian = unpack("C*",pack("S*",256));
            $bigEndian = !$testEndian[1]==1;
                        
            if ($bigEndian) $double = strrev($double);
            $double = unpack("d",$double);
            return $double[1];
        }

        /**
         * readLong 
         * 
         * @return int 
         */
        public function readLong() {

            $block = $this->readBuffer(4);
            $long = unpack("N",$block);
            return $long[1];
        }
       
    }


?>
