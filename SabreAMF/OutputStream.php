<?php

    /**
     * SabreAMF_OutputStream 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    class SabreAMF_OutputStream {

        /**
         * rawData 
         * 
         * @var string
         */
        private $rawData = '';

        /**
         * writeBuffer 
         * 
         * @param string $str 
         * @return void
         */
        public function writeBuffer($str) {
            $this->rawData.=$str;
        }

        /**
         * writeByte 
         * 
         * @param int $byte 
         * @return void
         */
        public function writeByte($byte) {

            $this->rawData.=pack('c',$byte);

        }

        /**
         * writeInt 
         * 
         * @param int $int 
         * @return void
         */
        public function writeInt($int) {

            $this->rawData.=pack('n',$int);

        }
        
        /**
         * writeString 
         * 
         * @param string $string 
         * @return void
         */
        public function writeString($string) {

            $this->writeInt(strlen($string));
            $this->rawData.=$string;

        }

        /**
         * writeLongString 
         * 
         * @param string $string 
         * @return void
         */
        public function writeLongString($string) {

            $this->writeLong(strlen($string));
            $this->rawData.=$string;

        }

        /**
         * writeDouble 
         * 
         * @param float $double 
         * @return void
         */
        public function writeDouble($double) {

            $bin = pack("d",$double);
            $testEndian = unpack("C*",pack("S*",256));
            $bigEndian = !$testEndian[1]==1;
            if ($bigEndian) $bin = strrev($bin);
            $this->rawData.=$bin;

        }

        /**
         * writeLong 
         * 
         * @param int $long 
         * @return void
         */
        public function writeLong($long) {

            $this->rawData.=pack("N",$long);


        }

        /**
         * getRawData 
         * 
         * @return string 
         */
        public function getRawData() {

            return $this->rawData;

        }


    }


?>
