<?php

    require_once dirname(__FILE__) . '/Const.php';
    require_once dirname(__FILE__) . '/../Const.php';
    require_once dirname(__FILE__) . '/../Deserializer.php';
    require_once dirname(__FILE__) . '/../AMF3/Deserializer.php';
    require_once dirname(__FILE__) . '/../AMF3/Wrapper.php';
    require_once dirname(__FILE__) . '/../TypedObject.php';

    /**
     * SabreAMF_AMF0_Deserializer 
     * 
     * @package SabreAMF
     * @subpackage AMF0
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    class SabreAMF_AMF0_Deserializer extends SabreAMF_Deserializer {

        /**
         * objectcount 
         * 
         * @var int
         */
        private $objectcount;
        /**
         * refList 
         * 
         * @var array 
         */
        private $refList;

        /**
         * readAMFData 
         * 
         * @param mixed $settype 
         * @return mixed 
         */
        public function readAMFData($settype = null) {

           if (is_null($settype)) {
                $settype = $this->stream->readByte();
           }

           switch ($settype) {

                case SabreAMF_AMF0_Const::DT_NUMBER      : return $this->stream->readDouble();
                case SabreAMF_AMF0_Const::DT_BOOL        : return $this->stream->readByte()==true;
                case SabreAMF_AMF0_Const::DT_STRING      : return $this->readString();
                case SabreAMF_AMF0_Const::DT_OBJECT      : return $this->readObject();
                case SabreAMF_AMF0_Const::DT_NULL        : return null; 
                case SabreAMF_AMF0_Const::DT_UNDEFINED   : return null;
                //case self::AT_REFERENCE   : return $this->readReference();
                case SabreAMF_AMF0_Const::DT_MIXEDARRAY  : return $this->readMixedArray();
                case SabreAMF_AMF0_Const::DT_ARRAY       : return $this->readArray();
                case SabreAMF_AMF0_Const::DT_DATE        : return $this->readDate();
                case SabreAMF_AMF0_Const::DT_LONGSTRING  : return $this->readLongString();
                case SabreAMF_AMF0_Const::DT_UNSUPPORTED : return null;
                case SabreAMF_AMF0_Const::DT_XML         : return $this->readLongString();
                case SabreAMF_AMF0_Const::DT_TYPEDOBJECT : return $this->readTypedObject();
                case SabreAMF_AMF0_Const::DT_AMF3        : return $this->readAMF3Data();
                default                   :  throw new Exception('Unsupported type: 0x' . strtoupper(str_pad(dechex($settype),2,0,STR_PAD_LEFT))); return false;
 
           }

        }

        /**
         * readObject 
         * 
         * @return object 
         */
        public function readObject() {

            $object = array();
            while (true) {
                $key = $this->readString();
                $vartype = $this->stream->readByte();
                if ($vartype==SabreAMF_AMF0_Const::DT_OBJECTTERM) break;
                $object[$key] = $this->readAmfData($vartype);
            }
            return (object)$object;    

        }

        /**
         * readArray 
         * 
         * @return array 
         */
        public function readArray() {

            $length = $this->stream->readLong();
            $arr = array();
            while($length--) $arr[] = $this->readAMFData();
            return $arr;

        }

        /**
         * readMixedArray 
         * 
         * @return array 
         */
        public function readMixedArray() {

            $highestIndex = $this->stream->readLong();
            return $this->readObject();

        }

        /**
         * readString 
         * 
         * @return string 
         */
        public function readString() {

            $strLen = $this->stream->readInt();
            return $this->stream->readBuffer($strLen);

        }

        /**
         * readLongString 
         * 
         * @return string 
         */
        public function readLongString() {

            $strLen = $this->stream->readLong();
            return $this->stream->readBuffer($strLen);

        }

        /**
         *  
         * readDate 
         * 
         * @return int 
         */
        public function readDate() {

            $timestamp = floor($this->stream->readDouble() / 1000);
            $timezoneOffset = $this->stream->readInt();
            if ($timezoneOffset > 720) $timezoneOffset = ((65536 - $timezoneOffset));
            $timezoneOffset=($timezoneOffset * 60) - date('Z');
            return $timestamp + ($timezoneOffset);


        }

        /**
         * readTypedObject 
         * 
         * @return object
         */
        public function readTypedObject() {

            return new SabreAMF_TypedObject($this->readString(),$this->readObject());

        }
        
        /**
         * readAMF3Data 
         * 
         * @return SabreAMF_AMF3_Wrapper 
         */
        public function readAMF3Data() {

            $deserializer = new SabreAMF_AMF3_Deserializer($this->stream);
            return new SabreAMF_AMF3_Wrapper($deserializer->readAMFData());

        }


   }

?>
