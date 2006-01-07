<?php

    include dirname(__FILE__) . '/Common.php';

    class SabreAMFDeserializer {

        private $rawData;
        private $currentByte;

        function __construct($rawdata) {

            $this->rawData = $rawdata;
            $this->currentByte = 0;

        }

        function readByte() {
            $data = ord($this->rawData[$this->currentByte]);
            $this->currentByte++;
            return $data;
        }

        function readInt() {
            return (($this->readByte() << 8) | $this->readByte());
        } 
        
        function readString() {
            $length = $this->readInt();
            $string = substr($this->rawData,$this->currentByte,$length);
            $this->currentByte+=$length;
            return ($string); 
        } 

        function readLong() {
            $long = ($this->readByte() << 24) | ($this->readByte() << 16) | ($this->readByte() << 8) | ($this->readByte());
            return $long;
        }  

        function readDouble() {
            if ($this->isBigEndian()) {
                $invertedBytes = ""; 
                for($i = 7 ; $i >= 0 ; $i--) { 
                    $invertedBytes .= $this->rawData[$this->currentByte + $i];
                } 
            } else {
                $invertedBytes = ""; 
                for($i = 0 ; $i < 8 ; $i++) { 
                    $invertedBytes .= $this->rawData[$this->currentByte + $i];
                }
            }
            $this->currentByte += 8;
            $double = unpack("dflt", $invertedBytes); 
            return $double['flt']; 
        }  

        function readObject() {
            $objectData = array(); 
            $key = $this->readString();
            for ($varType = $this->readByte(); $varType != AMF_TYPE_OBJECTTERMINATOR; $varType = $this->readByte()) {
                $value = $this->readData($varType); 
                $objectData[$key] = $value; 
                $key = $this->readString();
            } 
            return $objectData;
       } 

       function readArray() {
           $arrayData = array();
           $arrayLength = $this->readLong(); 
           for ($i = 0; $i < $arrayLength; $i++) { 
                $varType = $this->readByte(); 
                $arrayData[] = $this->readData($varType); 
           }
           return $arrayData; 
        
       } 

       function readCustomClass() {
           $type = $this->readString();
           return $this->readObject();
       }
        

       function readData($type) {

            switch ($type) {
                case AMF_TYPE_NUMBER      : return $this->readDouble();
                case AMF_TYPE_BOOLEAN     : return $this->readByte()==1; 
                case AMF_TYPE_STRING      : return $this->readString();
                case AMF_TYPE_OBJECT      : return $this->readObject();
                case AMF_TYPE_NULL        : return null;
                case AMF_TYPE_UNDEFINED   : return null;
//                case AMF_TYPE_FLUSHEDSO   : return $this->readFlushedSO();
//                case AMF_TYPE_MIXEDARRAY  : return $this->readMixedArray();
                case AMF_TYPE_ARRAY       : return $this->readArray();
//                case AMF_TYPE_DATE        : return $this->readData();
//                case AMF_TYPE_ASOBJECT    : return $this->readASObject();
//                case AMF_TYPE_XML         : return $this->readXML();
                case AMF_TYPE_CUSTOMCLASS : return $this->readCustomClass();
                default                   : die('Unhandled type: ' . $type);
            }
        }

        protected function isBigEndian() {
		    $tmp = pack("d", 1); 
            return ($tmp == "\0\0\0\0\0\0\360\77");
        }

    }

?>
