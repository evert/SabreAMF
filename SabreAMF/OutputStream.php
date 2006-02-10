<?php

    /* $Id$ */

    class SabreAMF_OutputStream {

        private $rawData = '';

        public function writeByte($byte) {

            $this->rawData.=pack('c',$byte);

        }

        public function writeInt($int) {

            $this->rawData.=pack('n',$int);

        }
        
        public function writeString($string) {

            $this->writeInt(strlen($string));
            $this->rawData.=$string;

        }

        public function writeLongString($string) {

            $this->writeLong(strlen($string));
            $this->rawData.=$string;

        }

        public function writeDouble($double) {

            $bin = pack("d",$double);
            $testEndian = unpack("C*",pack("S*",256));
            $bigEndian = !$testEndian[1]==1;
            if ($bigEndian) $bin = strrev($bin);
            $this->rawData.=$bin;

        }

        public function writeLong($long) {

            $this->rawData.=pack("N",$long);


        }

        public function getRawData() {

            return $this->rawData;

        }


    }


?>
