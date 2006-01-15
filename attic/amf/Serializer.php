<?php


    class SabreAMFSerializer {

        private $rawData='';

        public function __construct() {

        }

        public function writeByte($byte) {
            $this->rawData .= pack("c", $byte); 
        } 

        public function writeLong($long) {

            $this->rawData .= pack("N", $long); 
        
        }

        public function writeString($string) {

            $this->writeInt(strlen($string));
            $this->rawData .= $string; 
 
        }

        public function writeInt($int) {
            $this->rawData .= pack("n", $int); 
        }

        public function writeBoolean($boolean) {

            $this->writeByte($boolean==true);
    
        }
       
        public function writeCustomClass ($name, $d) {
            $this->writeString($name);
            $this->writeObject($d);
        }


        public function writeData($data,$type=-1) {

            if ($type == -1 && is_numeric($data)) $type = AMF_TYPE_NUMBER;
            if ($type == -1 && is_string($data))  $type = AMF_TYPE_STRING;
            if ($type == -1 && is_bool($data))    $type = AMF_TYPE_BOOLEAN;
            if ($type == -1 && is_array($data))   $type = AMF_TYPE_ARRAY;
            if ($type == -1 && is_object($data))  $type = AMF_TYPE_OBJECT;
            if ($type == -1 && is_null($data))    $type = AMF_TYPE_NULL;

            if ($type==-1) die('Could not autodetect type');
           
            $this->writeByte($type);
           
            switch($type) {

                case AMF_TYPE_BOOLEAN     : $this->writeBoolean($data==true); break;
                case AMF_TYPE_STRING      : $this->writeString($data); break;
                case AMF_TYPE_CUSTOMCLASS : $this->writeCustomClass($data,getType($data)); break;
                default : die('Unreconized type: ' . $type);

            }


        }


        public function getRawData() {
            return $this->rawData;

        }


        protected function isBigEndian() {
		    $tmp = pack("d", 1); 
            return ($tmp == "\0\0\0\0\0\0\360\77");
        }

    }

?>
