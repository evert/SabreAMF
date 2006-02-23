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
         * readLong 
         * 
         * @return int 
         */

        public function readLong() {

            $block = $this->readBuffer(4);
            $long = unpack("N",$block);
            return $long[1];
        }

        /**
         * readString 
         * 
         * @return string 
         */
        public function readString() {

            $strLen = $this->readInt();
            return $this->readBuffer($strLen);

        }

        /**
         * readLongString 
         * 
         * @return string 
         */
        public function readLongString() {

            $strLen = $this->readLong();
            return $this->readBuffer($strLen);

        }

    }


?>
