<?php

    /* $Id$ */

    class SabreAMF_InputStream {

        private $cursor = 0;
        private $rawData = '';


        public function __construct($data) {

            $this->rawData = $data;

        }

        private function &readBuffer($length) {

            if ($length+$this->cursor > strlen($this->rawData)) {
                throw new Exception('Buffer underrun at position: '. $this->cursor . '. Trying to fetch '. $length . ' bytes');
                return false;
            }
            $data = substr($this->rawData,$this->cursor,$length);
            $this->cursor+=$length;
            return $data;

        }

        public function readByte() {

            return ord($this->readBuffer(1));

        }

        public function readInt() {

            $block = $this->readBuffer(2);
            $int = unpack("n",$block);
            return $int[1];
        }


        public function readLong() {

            $block = $this->readBuffer(4);
            $long = unpack("N",$block);
            return $long[1];
        }

        public function readString() {

            $strLen = $this->readInt();
            return $this->readBuffer($strLen);

        }

        public function readLongString() {

            $strLen = $this->readLong();
            return $this->readBuffer($strLen);

        }

        public function readDouble() {

            $double = $this->readBuffer(8);

            $testEndian = unpack("C*",pack("S*",256));
            $bigEndian = !$testEndian[1]==1;
                        
            if ($bigEndian) $double = strrev($double);
            $double = unpack("d",$double);
            return $double[1];
        }

    }


?>
