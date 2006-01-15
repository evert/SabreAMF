<?php

    class SabreAMF_Deserializer {

        private $stream;
        private $objectcount;
        private $refList;

        public function __construct(SabreAMF_InputStream $stream) {

            $this->stream = $stream;

        }

        public function readAMFData($settype = null) {

           if (is_null($settype)) {
                $settype = $this->stream->readByte();
           }

           switch ($settype) {

                case SabreAMF_Const::AT_NUMBER      : return $this->stream->readDouble();
                case SabreAMF_Const::AT_BOOL        : return $this->stream->readByte()==true;
                case SabreAMF_Const::AT_STRING      : return $this->stream->readString();
                case SabreAMF_Const::AT_OBJECT      : return $this->readObject();
                //case self::AT_NULL        : return null; 
                //case self::AT_UNDEFINED   : return null;
                //case self::AT_REFERENCE   : return $this->readReference();
                //case self::AT_MIXEDARRAY  : return $this->readMixedArray();
                case SabreAMF_Const::AT_ARRAY       : return $this->readArray();
                //case self::AT_DATE        : return $this->readDate();
                //case self::AT_LONGSTRING  : return $this->stream->readLongString();
                //case self::AT_UNSUPPORTED : return null;
                //case self::AT_XML         : return $this->stream->readLongString();
                //case self::AT_NAMEDCLASS  : return $this->readNamedClass();
                default                   :  throw new Exception('Unsupported type: 0x' . strtoupper(str_pad(dechex($settype),2,0,STR_PAD_LEFT))); return false;
 
           }

        }

        public function readObject() {

            $object = array();
            while (true) {
                $key = $this->stream->readString();
                $vartype = $this->stream->readByte();
                if ($vartype==SabreAMF_Const::AT_OBJECTTERM) break;
                $object[$key] = $this->readAmfData($vartype);
            }
            return $object;    

        }

        public function readArray() {

            $length = $this->stream->readLong();
            $arr = array();
            while($length--) $arr[] = $this->readAMFData();
            return $arr;

        }


    }

?>
