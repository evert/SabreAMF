<?php

    require_once dirname(__FILE__) . '/Const.php';
    require_once dirname(__FILE__) . '/../Const.php';
    require_once dirname(__FILE__) . '/../Serializer.php';
    require_once dirname(__FILE__) . '/../AMF3/Serializer.php';
    require_once dirname(__FILE__) . '/../AMF3/Wrapper.php';
    require_once dirname(__FILE__) . '/../ITypedObject.php';

    /**
     * SabreAMF_AMF0_Serializer 
     * 
     * @package SabreAMF
     * @subpackage AMF0
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    class SabreAMF_AMF0_Serializer extends SabreAMF_Serializer {

        /**
         * writeAMFData 
         * 
         * @param mixed $data 
         * @param int $forcetype 
         * @return mixed 
         */
        public function writeAMFData($data,$forcetype=null) {

           if (is_null($forcetype)) {
                $type=false;
                if (!$type && is_null($data))    $type = SabreAMF_AMF0_Const::DT_NULL;
                if (!$type && is_bool($data))    $type = SabreAMF_AMF0_Const::DT_BOOL;
                if (!$type && is_numeric($data)) $type = SabreAMF_AMF0_Const::DT_NUMBER;
                if (!$type && is_string($data) && strlen($data)>65536) $type = SabreAMF_Const::DT_LONGSTRING;
                if (!$type && is_string($data))  $type = SabreAMF_AMF0_Const::DT_STRING;
                if (!$type && is_array($data))   $type = SabreAMF_AMF0_Const::DT_MIXEDARRAY;
                if (!$type && is_object($data)) {
                    if($data instanceof SabreAMF_ITypedObject) $type = SabreAMF_AMF0_Const::DT_TYPEDOBJECT;
                    else if ($data instanceof SabreAMF_AMF3_Wrapper) $type = SabreAMF_AMF0_Const::DT_AMF3;
                    else $type = SabreAMF_AMF0_Const::DT_OBJECT;
                }
                if ($type===false) {
                    throw new Exception('Unhandled data-type: ' . gettype($data));
                    return null;
                }
           } else $type = $forcetype;

           $this->stream->writeByte($type);

           switch ($type) {

                case SabreAMF_AMF0_Const::DT_NUMBER      : return $this->stream->writeDouble($data);
                case SabreAMF_AMF0_Const::DT_BOOL        : return $this->stream->writeByte($data==true);
                case SabreAMF_AMF0_Const::DT_STRING      : return $this->stream->writeString($data);
                case SabreAMF_AMF0_Const::DT_OBJECT      : return $this->writeObject($data);
                case SabreAMF_AMF0_Const::DT_NULL        : return true; 
                //case self::AT_REFERENCE   : return $this->readReference();
                case SabreAMF_AMF0_Const::DT_MIXEDARRAY  : return $this->writeMixedArray($data);
                case SabreAMF_AMF0_Const::DT_LONGSTRING  : return $this->stream->writeLongString();
                case SabreAMF_AMF0_Const::DT_TYPEDOBJECT : return $this->writeTypedObject($data);
                case SabreAMF_AMF0_Const::DT_AMF3        : return $this->writeAMF3Data($data);
                default                   :  throw new Exception('Unsupported type: ' . gettype($data)); return false;
 
           }

        }

        /**
         * writeMixedArray 
         * 
         * @param array $data 
         * @return void
         */
        public function writeMixedArray($data) {

            $this->stream->writeLong(0);
            foreach($data as $key=>$value) {
                $this->stream->writeString($key);
                $this->writeAMFData($value);
            }
            $this->stream->writeString('');
            $this->stream->writeByte(SabreAMF_AMF0_Const::DT_OBJECTTERM);

        }

        /**
         * writeObject 
         * 
         * @param object $data 
         * @return void
         */
        public function writeObject($data) {

            foreach($data as $key=>$value) {
                $this->stream->writeString($key);
                $this->writeAmfData($value);
            }
            $this->stream->writeString('');
            $this->stream->writeByte(SabreAMF_AMF0_Const::DT_OBJECTTERM);
            return true;

        }

        /**
         * writeTypedObject 
         * 
         * @param SabreAMF_ITypedObject $data 
         * @return void
         */
        public function writeTypedObject(SabreAMF_ITypedObject $data) {

            $this->stream->writeString($data->getAMFClassName());
            return $this->writeObject($data->getAMFData());

        }


        /**
         * writeAMF3Data 
         * 
         * @param mixed $data 
         * @return void 
         */
        public function writeAMF3Data(SabreAMF_AMF3_Wrapper $data) {

            $serializer = new SabreAMF_AMF3_Serializer($this->stream);
            return $serializer->writeAMFData($data->getData());

        }

    }

?>
