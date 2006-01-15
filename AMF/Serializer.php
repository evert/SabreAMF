<?php

    class SabreAMF_Serializer {

        private $stream;

        public function __construct(SabreAMF_OutputStream $stream) {

            $this->stream = $stream;

        }

        public function writeAMFData($data,$forcetype=null) {

           if (is_null($forcetype)) {
                $type=false;
                if (!$type && is_bool($data))    $type = SabreAMF_Const::AT_BOOL;
                if (!$type && is_int($data))     $type = SabreAMF_Const::AT_NUMBER;
                if (!$type && is_float($data))   $type = SabreAMF_Const::AT_NUMBER;
                if (!$type && is_numeric($data)) $type = SabreAMF_Const::AT_NUMBER;
                if (!$type && is_string($data))  $type = SabreAMF_Const::AT_STRING;
                if (!$type && is_array($data))   $type = SabreAMF_Const::AT_MIXEDARRAY;
           } else $type = $forcetype;

           $this->stream->writeByte($type);

           switch ($type) {

                case SabreAMF_Const::AT_NUMBER      : return $this->stream->writeDouble($data);
                case SabreAMF_Const::AT_BOOL        : return $this->stream->writeByte($data==true);
                case SabreAMF_Const::AT_STRING      : return $this->stream->writeString($data);
                //case self::AT_OBJECT      : return $this->readObject();
                //case self::AT_NULL        : return null; 
                //case self::AT_UNDEFINED   : return null;
                //case self::AT_REFERENCE   : return $this->readReference();
                case SabreAMF_Const::AT_MIXEDARRAY  : return $this->writeMixedArray($data);
                //case self::AT_ARRAY       : return $this->readArray();
                //case self::AT_DATE        : return $this->readDate();
                //case self::AT_LONGSTRING  : return $this->stream->readLongString();
                //case self::AT_UNSUPPORTED : return null;
                //case self::AT_XML         : return $this->stream->readLongString();
                //case self::AT_NAMEDCLASS  : return $this->readNamedClass();
                default                   :  throw new Exception('Unsupported type: 0x' . strtoupper(str_pad(dechex($type),2,0,STR_PAD_LEFT))); return false;
 
           }

        }

        public function writeMixedArray($data) {

            $this->stream->writeLong(0);
            foreach($data as $key=>$value) {
                $this->stream->writeString($key);
                $this->writeAMFData($value);
            }
            $this->stream->writeString('');
            $this->stream->writeByte(SabreAMF_Const::AT_OBJECTTERM);

        }


    }

?>
