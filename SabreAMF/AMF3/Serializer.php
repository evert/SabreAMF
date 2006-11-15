<?php

    require_once 'SabreAMF/AMF3/Const.php';
    require_once 'SabreAMF/Const.php';
    require_once 'SabreAMF/Serializer.php';
    require_once 'SabreAMF/ITypedObject.php';
    require_once 'SabreAMF/ByteArray.php';

    /**
     * SabreAMF_AMF3_Serializer 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @author Karl von Randow http://xk72.com/
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     * @uses SabreAMF_Const
     * @uses SabreAMF_AMF3_Const
     * @uses SabreAMF_ITypedObject
     */
    class SabreAMF_AMF3_Serializer extends SabreAMF_Serializer {

        /**
         * writeAMFData 
         * 
         * @param mixed $data 
         * @param int $forcetype 
         * @return mixed 
         */
        public function writeAMFData($data,$forcetype=null) {

           if (is_null($forcetype)) {
               // Autodetecting data type
               $type=false;
               if (!$type && is_null($data))    $type = SabreAMF_AMF3_Const::DT_NULL;
               if (!$type && is_bool($data))    {
                    $type = $data?SabreAMF_AMF3_Const::DT_BOOL_TRUE:SabreAMF_AMF3_Const::DT_BOOL_FALSE;
                }
                if (!$type && is_int($data))     $type = SabreAMF_AMF3_Const::DT_INTEGER;
                if (!$type && is_float($data))   $type = SabreAMF_AMF3_Const::DT_NUMBER;
                if (!$type && is_numeric($data)) $type = SabreAMF_AMF3_Const::DT_INTEGER;
                if (!$type && is_string($data))  $type = SabreAMF_AMF3_Const::DT_STRING;
                if (!$type && is_array($data))   {
                    $type = SabreAMF_AMF3_Const::DT_ARRAY;
                    foreach($data as $k=>$v) if (!is_numeric($k)) $type = SabreAMF_AMF3_Const::DT_OBJECT; 
                }
                if (!$type && is_object($data)) {

                    if ($data instanceof SabreAMF_ByteArray) {
                        $type = SabreAMF_AMF3_Const::DT_BYTEARRAY;
                    } else {
                        $type = SabreAMF_AMF3_Const::DT_OBJECT;
                    }

                }
                if ($type===false) {
                    throw new Exception('Unhandled data-type: ' . gettype($data));
                    return null;
                }
                if ($type == SabreAMF_AMF3_Const::DT_INTEGER && ($data > 268435455 || $data < -268435456)) {
                	$type = SabreAMF_AMF3_Const::DT_NUMBER;
                }
           } else $type = $forcetype;

           $this->stream->writeByte($type);

           switch ($type) {

                case SabreAMF_AMF3_Const::DT_NULL        : break;
                case SabreAMF_AMF3_Const::DT_BOOL_FALSE  : break;
                case SabreAMF_AMF3_Const::DT_BOOL_TRUE   : break;
                case SabreAMF_AMF3_Const::DT_INTEGER     : $this->writeInt($data); break;
                case SabreAMF_AMF3_Const::DT_NUMBER      : $this->stream->writeDouble($data); break;
                case SabreAMF_AMF3_Const::DT_STRING      : $this->writeString($data); break;
                case SabreAMF_AMF3_Const::DT_ARRAY       : $this->writeArray($data); break;
                case SabreAMF_AMF3_Const::DT_OBJECT      : $this->writeObject($data); break; 
                case SabreAMF_AMF3_Const::DT_BYTEARRAY   : $this->writeByteArray($data); break;
                default                   :  throw new Exception('Unsupported type: ' . gettype($data)); return null; 
 
           }

        }

        /**
         * writeObject 
         * 
         * @param mixed $data 
         * @return void
         */
        public function writeObject($data) {

            if ($data instanceof SabreAMF_ITypedObject) {

                $classname = $data->getAMFClassName();
                $data = $data->getAMFData();

            } else if (!$classname = $this->getRemoteClassName(get_class($data))) {

                $classname = '';

            }


            $refId = SabreAMF_AMF3_Const::ET_OBJ_INLINE | SabreAMF_AMF3_Const::ET_CLASS_INLINE; 
           
            $count=0;
            foreach($data as $k=>$v) {
                $count++;
            }

            //echo("bcount: " . $count . "\n");

            $refId = $refId | ($count << 4);

            $this->writeInt($refId);

            $this->writeString($classname);

            foreach($data as $k=>$v) {

                $this->writeString($k);

            }
            foreach($data as $k=>$v) {

                $this->writeAMFData($v);

            }
            //$this->writeString('');

        }

        /**
         * writeInt 
         * 
         * @param int $int 
         * @return void
         */
        public function writeInt($int) {

            $count = 0;
            $bytes = array();
            if (($int & 0xff000000) != 0) {
            	$bytes[] = $int & 0xFF;
            	for($i=0;$i<3;$i++) {
	                $bytes[] = ($int >> (8 + 7*$i)) & 0x7F;
	            }
            } else {
	            for($i=0;$i<4;$i++) {
	                $bytes[] = ($int >> (7*$i)) & 0x7F;
	            }
            }
            $bytes = array_reverse($bytes);
            while(count($bytes)>1 && $bytes[0] == 0) {
                array_shift($bytes);
            }
            foreach($bytes as $k=>$byte) {
                if ($k<count($bytes)-1) $byte = $byte | 0x80;
                $this->stream->writeByte($byte);
            }    

        }

        public function writeByteArray(SabreAMF_ByteArray $data) {

            $this->writeString($data->getData());

        }

        /**
         * writeString 
         * 
         * @param string $str 
         * @return void
         */
        public function writeString($str) {

            $strref = strlen($str) << 1 | 0x01;
            $this->writeInt($strref);
            $this->stream->writeBuffer($str);

        }

        /**
         * writeArray 
         * 
         * @param array $arr 
         * @return void
         */
        public function writeArray($arr) {

            end($arr);
            $arrLen = count($arr); 
         
            $arrId = ($arrLen << 1) | 0x01;
            $this->writeInt($arrId);
            $this->writeInt(1); // Not sure what this is 
           
            foreach($arr as $v) {
                $this->writeAMFData($v);
            }

        }
        

    }

?>
