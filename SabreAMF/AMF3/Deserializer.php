<?php

    require_once dirname(__FILE__) . '/Const.php';
    require_once dirname(__FILE__) . '/../Const.php';
    require_once dirname(__FILE__) . '/../TypedObject.php';
    require_once dirname(__FILE__) . '/../Deserializer.php';


    /**
     * SabreAMF_AMF3_Deserializer 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
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
        
                // Check if class is stored
                
                if (($classref & 0x01) == 0) {
                    die('Stored class');
                } else {
                    $classname = $this->readString();
                }

                $objType = ($classref>>1) & 0x03;

                // Check to see the encoding type
                if (($objType & 2)==2) {
                    // Property-value pairs
                    $obj = array();
                    do {
                        $propertyName = $this->readString();
                        if ($propertyName!='' && !is_null($propertyName)) {
                            $propValue = $this->readAMFData();
                            $obj[$propertyName] = $propValue;
                        }
                    } while($propertyName !='');
                 } else {
                    $propertyCount = $classref >> 3;
                
                     $obj = array();
                     $propertyNames = array();
                     if (($objType & 1)==1) {
                          // One single value, no propertyname. Not sure what to do with this, so following ServiceCapture's example and naming the property 'source'
                          $propertyNames[] = 'source';
                     } else {
                        //None of the above. First read all the propertynames, then the values 
                        for($i=0;$i<$propertyCount;$i++) {
                            $propertyName = $this->readString();
                             $propertyNames[] = $propertyName;
                        }
                     }
                     foreach($propertyNames as $pn) {
                         $obj[$pn] = $this->readAMFData();
                     }
                }
                if ($classname) {
                    $obj = new SabreAMF_TypedObject($classname,$obj);
                }
                $this->storedObjects[] = $obj;
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
         * readInt 
         * 
         * @return int 
         */
        public function readInt() {

            $count = 1;
            $int = 0;

            $byte = $this->stream->readByte();

            while(($byte >> 7 == 1) && $count < 4) {
                $int = ($int  | (($byte & 0x7F) << ($count*7)));
                $byte = $this->stream->readByte();
                echo("Read: $byte\n");
                $count++;
            }
            $int = $int | $byte;

/*
            //Negative values
            if (($int >> 27)==1) {
                $int = $int | 0xF0000000;
            }
*/
            return $int;
         
        }

        /**
         * readDate 
         * 
         * @return int 
         */
        public function readDate() {
            $timeOffset = $this->readInt();
            if (($timeOffset & 0x01) == 0) {
                $dateRef = $timeOffset >> 1;
                if ($dateRef>=count($this->storedObjects)) {
                    throw new Exception('Undefined date reference: ' . $dateRef);
                    return false;
                }
                return $this->storedObjects[$arrId];
            }
            $timeOffset = ($timeOffset >> 1) * 6000 * -1;
            $ms = $this->stream->readDouble();

            $date = $ms-$timeOffset;
            $this->storedObjects[] = $date;
            return $date;
        }
 

    }

?>
