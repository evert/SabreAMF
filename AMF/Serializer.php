<?php

    require_once dirname(__FILE__) . '/Const.php';

    class SabreAMF_Serializer {

        private $stream;

        public function __construct(SabreAMF_OutputStream $stream) {

            $this->stream = $stream;

        }

        public function writeAMFData($data,$forcetype=null) {

           if (is_null($forcetype)) {
                $type=false;
                if (!$type && is_null($data))    $type = SabreAMF_Const::AT_NULL;
                if (!$type && is_bool($data))    $type = SabreAMF_Const::AT_BOOL;
                if (!$type && is_int($data))     $type = SabreAMF_Const::AT_NUMBER;
                if (!$type && is_float($data))   $type = SabreAMF_Const::AT_NUMBER;
                if (!$type && is_numeric($data)) $type = SabreAMF_Const::AT_NUMBER;
                if (!$type && is_string($data) && strlen($data)>65536) $type = SabreAMF_Const::AT_LONGSTRING;
                if (!$type && is_string($data))  $type = SabreAMF_Const::AT_STRING;
                if (!$type && is_array($data))   $type = SabreAMF_Const::AT_MIXEDARRAY;
                if (!$type && is_object($data) && $data instanceof SabreAMF_TypedObject) $type = SabreAMF_Const::AT_TYPEDOBJECT;
                if (!$type && is_object($data))  $type = SabreAMF_Const::AT_OBJECT;
                if ($type===false) $type = 0xFF;
           } else $type = $forcetype;

           $this->stream->writeByte($type);

           switch ($type) {

                case SabreAMF_Const::AT_NUMBER      : return $this->stream->writeDouble($data);
                case SabreAMF_Const::AT_BOOL        : return $this->stream->writeByte($data==true);
                case SabreAMF_Const::AT_STRING      : return $this->stream->writeString($data);
                case SabreAMF_Const::AT_OBJECT      : return $this->writeObject($data);
                case SabreAMF_Const::AT_NULL        : return true; 
                //case self::AT_REFERENCE   : return $this->readReference();
                case SabreAMF_Const::AT_MIXEDARRAY  : return $this->writeMixedArray($data);
                case SabreAMF_Const::AT_LONGSTRING  : return $this->stream->writeLongString();
                case SabreAMF_Const::AT_TYPEDOBJECT : return $this->writeTypedObject($data);
                default                   :  throw new Exception('Unsupported type: ' . gettype($data)); return false;
 
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

        public function writeObject($data) {

            foreach($data as $key=>$value) {
                $this->stream->writeString($key);
                $this->writeAmfData($value);
            }
            $this->stream->writeString('');
            $this->stream->writeByte(SabreAMF_Const::AT_OBJECTTERM);
            return true;

        }

        public function writeTypedObject($data) {

            $this->stream->writeString($data->getAMFClassName());
            return $this->writeObject($data->getAMFData());

        }

    }

?>
