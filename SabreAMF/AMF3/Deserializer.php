<?php

    require_once 'SabreAMF/AMF3/Const.php';
    require_once 'SabreAMF/Const.php';
    require_once 'SabreAMF/TypedObject.php';
    require_once 'SabreAMF/Deserializer.php';
    require_once 'SabreAMF/ByteArray.php';
    require_once 'SabreAMF/Externalized.php';

    /**
     * SabreAMF_AMF3_Deserializer 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id$
     * @copyright 2006-2007 Rooftop Solutions
     * @author Evert Pot (http://www.rooftopsolutions.nl)
     * @author Karl von Randow http://xk72.com/
     * @author Jim Mischel
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     * @uses SabreAMF_Const
     * @uses SabreAMF_AMF3_Const
     * @uses SabreAMF_TypedObject
     */
    class SabreAMF_AMF3_Deserializer extends SabreAMF_Deserializer {

        /**
         * objectcount 
         * 
         * @var int
         */
        private $objectcount;

        /**
         * storedStrings 
         * 
         * @var array 
         */
        private $storedStrings = array();

        /**
         * storedObjects 
         * 
         * @var array 
         */
        private $storedObjects = array();

        /**
         * storedClasses 
         * 
         * @var array
         */
        private $storedClasses = array();

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

                case SabreAMF_AMF3_Const::DT_UNDEFINED  : return null; 
                case SabreAMF_AMF3_Const::DT_NULL       : return null; 
                case SabreAMF_AMF3_Const::DT_BOOL_FALSE : return false;
                case SabreAMF_AMF3_Const::DT_BOOL_TRUE  : return true;
                case SabreAMF_AMF3_Const::DT_INTEGER    : return $this->readInt();
                case SabreAMF_AMF3_Const::DT_NUMBER     : return $this->stream->readDouble();
                case SabreAMF_AMF3_Const::DT_STRING     : return $this->readString();
                case SabreAMF_AMF3_Const::DT_XML        : return $this->readString();
                case SabreAMF_AMF3_Const::DT_DATE       : return $this->readDate();
                case SabreAMF_AMF3_Const::DT_ARRAY      : return $this->readArray();
                case SabreAMF_AMF3_Const::DT_OBJECT     : return $this->readObject();
                case SabreAMF_AMF3_Const::DT_XMLSTRING  : return $this->readXMLString();
                case SabreAMF_AMF3_Const::DT_BYTEARRAY  : return $this->readByteArray();
                default                   :  throw new Exception('Unsupported type: 0x' . strtoupper(str_pad(dechex($settype),2,0,STR_PAD_LEFT))); return false;


           }

        }


        /**
         * readObject 
         * 
         * @return object 
         */
        public function readObject() {

            $objInfo = $this->readInt();
            $storedObject = ($objInfo & 0x01)==0;
            $objInfo = $objInfo >> 1;

            if ($storedObject) {

                $objectReference = $objInfo;
                if (!isset($this->storedObjects[$objectReference])) {

                    throw new Exception('Object reference #' . $objectReference . ' not found');

                } else {

                    $rObject = $this->storedObject[$objectReference];

                }

            } else {

                $storedClass = ($objInfo & 0x01)==0;
                $objInfo= $objInfo >> 1;

                // If this is a stored  class.. we have the info
                if ($storedClass) {
                  
                    $classReference = $objInfo;
                    if (!isset($this->storedClasses[$classReference])) {

                        throw new Exception('Class reference #' . $classReference . ' not found');

                    } else {

                        $encodingType = $this->storedClasses[$classReference]['encodingType'];
                        $propertyNames = $this->storedClasses[$classReference]['propertyNames'];
                        $className = $this->storedClasses[$classReference]['className'];

                    }
                  
                } else { 

                    $className = $this->readString();
                    $encodingType = $objInfo & 0x03;
                    $propertyNames = array();
                    $objInfo = $objInfo >> 2;

                }
                  
                //ClassMapping magic
                if ($className) {

                    if ($localClassName = SabreAMF_ClassMapper::getLocalClass($className)) {

                        $rObject = new $localClassName();

                    } else {

                        $rObject = new SabreAMF_TypedObject($className,array());

                    }
                } else {

                    $rObject = new STDClass(); 

                }

                $this->storedObjects[] =& $rObject;

                if ($encodingType & SabreAMF_AMF3_Const::ET_EXTERNALIZED) {

                    if (!$storedClass) {
                        $this->storedClasses[] = array('className' => $className,'encodingType'=>$encodingType,'propertyNames'=>$propertyNames);
                    }
                    if ($rObject instanceof SabreAMF_Externalized) {
                        $rObject->readExternal($this->readAMFData());
                    } elseif ($rObject instanceof SabreAMF_TypedObject) {
                        $rObject->setAMFData(array('externalizedData'=>$this->readAMFData()));
                    } else {
                        $rObject->externalizedData = $this->readAMFData();
                    }
                    //$properties['externalizedData'] = $this->readAMFData();

                } else {

                    if ($encodingType & SabreAMF_AMF3_Const::ET_SERIAL) {

                        if (!$storedClass) {
                            $this->storedClasses[] = array('className' => $className,'encodingType'=>$encodingType,'propertyNames'=>$propertyNames);
                        }
                        $properties = array();
                        do {
                            $propertyName = $this->readString();
                            if ($propertyName!="") {
                                $propertyNames[] = $propertyName;
                                $properties[$propertyName] = $this->readAMFData();
                            }
                        } while ($propertyName!="");
                        
                        
                    } else {
                        if (!$storedClass) {
                            $propertyCount = $objInfo;
                            for($i=0;$i<$propertyCount;$i++) {

                                $propertyNames[] = $this->readString();

                            }
                            $this->storedClasses[] = array('className' => $className,'encodingType'=>$encodingType,'propertyNames'=>$propertyNames);

                        }

                        $properties = array();
                        foreach($propertyNames as $propertyName) {

                            $properties[$propertyName] = $this->readAMFData();

                        }

                    }
                    
                    if ($rObject instanceof SabreAMF_TypedObject) {
                        $rObject->setAMFData($properties);
                    } else {
                        foreach($properties as $k=>$v) if ($k) $rObject->$k = $v;
                    }

                }

            }
            return $rObject;

        }

        /**
         * readArray 
         * 
         * @return array 
         */
        public function readArray() {

            $arrId = $this->readInt();
            if (($arrId & 0x01)==0) {
                 $arrId = $arrId >> 1;
                 if ($arrId>=count($this->storedObjects)) {
                    throw new Exception('Undefined array reference: ' . $arrId);
                    return false;
                }
                return $this->storedObjects[$arrId]; 
            }
            $arrId = $arrId >> 1;
            
            $data = array();

            $this->stream->readByte();
    
            $this->storedObjects[] &= $data;

            for($i=0;$i<$arrId;$i++) {
                $data[] = $this->readAMFData();
            }

            return $data;

        }
        

        /**
         * readString 
         * 
         * @return string 
         */
        public function readString() {

            $strref = $this->readInt();

            if (($strref & 0x01) == 0) {
                $strref = $strref >> 1;
                if ($strref>=count($this->storedStrings)) {
                    throw new Exception('Undefined string reference: ' . $strref);
                    return false;
                }
                return $this->storedStrings[$strref];
            } else {
                $strlen = $strref >> 1; 
                $str = $this->stream->readBuffer($strlen);
                if ($str != "") $this->storedStrings[] = $str;
                return $str;
            }

        }
        

        /**
         * readString 
         * 
         * @return string 
         */
        public function readXMLString() {

            $strref = $this->readInt();

            $strlen = $strref >> 1; 
            $str = $this->stream->readBuffer($strlen);
            return simplexml_load_string($str);

        }

        /**
         * readString 
         * 
         * @return string 
         */
        public function readByteArray() {

            $strref = $this->readInt();

            $strlen = $strref >> 1; 
            $str = $this->stream->readBuffer($strlen);
            return new SabreAMF_ByteArray($str);

        }

        /**
         * readInt 
         * 
         * @return int 
         */
        public function readInt() {

            $count = 1;
            $int = 0;

            $byte = $this->stream->readByte();

			while((($byte & 0x80) != 0) && $count < 4) {
                $int <<= 7;
                $int |= ($byte & 0x7f);
                $byte = $this->stream->readByte();
                $count++;
            }
            
            if ($count < 4) {
            	$int <<= 7;
            	$int |= $byte;
            } else {
            	// Use all 8 bits from the 4th byte
            	$int <<= 8;
            	$int |= $byte;
            	
            	// Check if the integer should be negative
            	if (($int & 0x10000000) != 0) {
            		// and extend the sign bit
            		$int |= 0xe0000000;
            	}
            }
            
            return $int;
         
        }

        /**
         * readDate 
         * 
         * @return int 
         */
        public function readDate() {
            $dateref = $this->readInt();
            if (($dateref & 0x01) == 0) {
                $dateref = $dateref >> 1;
                if ($dateref>=count($this->storedObjects)) {
                    throw new Exception('Undefined date reference: ' . $dateref);
                    return false;
                }
                return $this->storedObjects[$dateref];
            }

            $timestamp = floor($this->stream->readDouble() / 1000);

            // nasty hack to create a date time object, based on a unix timestamp
            $dateTime = new DateTime(date(DATE_ATOM,$timestamp));
            
            $this->storedObjects[] = $dateTime;
            return $dateTime;
        }
 

    }

?>
