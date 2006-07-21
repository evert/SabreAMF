<?php

    require_once 'SabreAMF/AMF3/RemotingMessage.php';
    require_once 'SabreAMF/AMF3/CommandMessage.php';
    require_once 'SabreAMF/AMF3/AcknowledgeMessage.php';
    require_once 'SabreAMF/AMF3/Const.php';
    require_once 'SabreAMF/Const.php';
    require_once 'SabreAMF/TypedObject.php';
    require_once 'SabreAMF/Deserializer.php';


    /**
     * SabreAMF_AMF3_Deserializer 
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
                default                   :  throw new Exception('Unsupported type: 0x' . strtoupper(str_pad(dechex($settype),2,0,STR_PAD_LEFT))); return false;


           }

        }


        /**
         * readObject 
         * 
         * @return object 
         */
        public function readObject() {

            $objref = $this->readInt();

            // Check if object is stored
            if (($objref & 0x01)==0) {
                 $objref = $objref >> 1;
                 if ($objref>=count($this->storedObjects)) {
                    throw new Exception('Undefined object reference: ' . $objref);
                    return false;
                }
                return (object)$this->storedObjects[$objref]; 
            } else {
                $classref = $objref >> 1;
                $objType = ($classref>>1) & 0x03;
        
                // Check if class is stored
                
                if (($classref & 0x01) == 0) {
                    $classref = $classref >> 1;
                    if ($classref>=count($this->storedObjects)) {
	                    throw new Exception('Undefined class reference: ' . $classref);
	                    return false;
	                }
	                $classtemplate = $this->storedObjects[$classref];
	                if ($classtemplate instanceof SabreAMF_ITypedObject) {
						$classname = $classtemplate->getAMFClassName();	                	
	                } else {
	                	$classname = false;
	                }
                } else {
                    $classname = $this->readString();
                    $classtemplate = false;
                }
                
                // Create the values array and store it in the storedObjects
                // before reading the properties.
                $values = array();
                $isMapped = false;
                if ($classname) {
                    if ($localClass = $this->getLocalClassName($classname)) {
                        $obj = new $localClass();
                        $isMapped = true;
                    } else {   
                        $obj = new SabreAMF_TypedObject($classname,$values);
                    }
                } else {
                	$obj = &$values;
                }
                
                $this->storedObjects[] = $obj;

                // Check to see the encoding type
                switch ($objType) {
                	case 2:
                	{
                		if ($classtemplate) {
                			$propertyNames = array_keys($classtemplate->getAMFData());
                			for ($i=0; $i<count($propertyNames); $i++) {
                				$values[$propertyNames[$i]] = $this->readAMFData();
                			}
                		} else {
	                		// Property-value pairs
		                    do {
		                        $propertyName = $this->readString();
		                        if ($propertyName!='' && !is_null($propertyName)) {
		                            $propValue = $this->readAMFData();
		                            $values[$propertyName] = $propValue;
		                        }
		                    } while($propertyName !='');
                		}
                	}
                	break;
                	case 1:
                	{
                		// One single value, no propertyname. Not sure what to do with this, so following ServiceCapture's example and naming the property 'source'
                        $values["source"] = $this->readAMFData();
                	}
                	break;
                	case 0:
                	{
                		$propertyCount = $classref >> 3;
                		$propertyNames = array();
                		// First read all the propertynames, then the values 
                        for($i=0;$i<$propertyCount;$i++) {
                            $propertyName = $this->readString();
                             $propertyNames[] = $propertyName;
                        }
                        
                        foreach($propertyNames as $pn) {
	                        $values[$pn] = $this->readAMFData();
	                    }
                	}
                	break;
                }
                
                if ($isMapped) {
                    foreach($values as $k=>$v) {
                        $obj->$k = $v;
                    }
                } else if ($obj instanceof SabreAMF_ITypedObject) {
	        		$obj->setAMFData($values);
	            } 

                return (object)$obj;
                
            }

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
    

            for($i=0;$i<$arrId;$i++) {
                $data[] = $this->readAMFData();
            }

            $this->storedObjects[] = $data;
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
                return $this->storedStrings[$strref >> 1];
            } else {
                $strlen = $strref >> 1; 
                $str = $this->stream->readBuffer($strlen);
                $this->storedStrings[] = $str;
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
            return $str;

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
            //$timeOffset = ($dateref >> 1) * 6000 * -1;
            $ms = $this->stream->readDouble();

            //$date = $ms-$timeOffset;
            $date = $ms;
            
            $this->storedObjects[] = $date;
            return $date;
        }
 

    }

?>
