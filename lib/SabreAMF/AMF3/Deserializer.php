<?php

    /**
     * SabreAMF_AMF3_Deserializer 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id$
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
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

            $objInfo = $this->readU29();
            $storedObject = ($objInfo & 0x01)==0;
            $objInfo = $objInfo >> 1;

            if ($storedObject) {

                $objectReference = $objInfo;
                if (!isset($this->storedObjects[$objectReference])) {

                    throw new Exception('Object reference #' . $objectReference . ' not found');

                } else {

                    $rObject = $this->storedObjects[$objectReference];

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

                    if ($localClassName = $this->getLocalClassName($className)) {

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
                        if($rObject->getAMFClassName() == "DSK"){
                            $data = $this->readDSK()->getAMFData();
                            $rObject = $data;
                        }
                        elseif($rObject->getAMFClassName() == "DSA"){
                            $data = $this->readDSA()->getAMFData();
                            $rObject = $data;
                        }
                        else{
                            $rObject->setAMFData(array('externalizedData'=>$this->readAMFData()));
                        }
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

            $arrId = $this->readU29();
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
            $this->storedObjects[] &= $data;

            $key = $this->readString();

            while($key!="") {
                $data[$key] = $this->readAMFData();
                $key = $this->readString();
            }

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

            $strref = $this->readU29();

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

            $strref = $this->readU29();

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

            $strref = $this->readU29();

            $strlen = $strref >> 1; 
            $str = $this->stream->readBuffer($strlen);
            return new SabreAMF_ByteArray($str);

        }

        /**
         * readU29 
         * 
         * @return int
         */
        public function readU29() {

            $count = 1;
            $u29 = 0;

            $byte = $this->stream->readByte();
  
            while((($byte & 0x80) != 0) && $count < 4) {
                $u29 <<= 7;
                $u29 |= ($byte & 0x7f);
                $byte = $this->stream->readByte();
                $count++;
            }
            
            if ($count < 4) {
                $u29 <<= 7;
                $u29 |= $byte;
            } else {
                // Use all 8 bits from the 4th byte
                $u29 <<= 8;
                $u29 |= $byte;
            }
            
            return $u29;
         
        }

        /**
         * readInt
         *
         * @return int
         */
        public function readInt() {
            
            $int = $this->readU29();
            // if int and has the sign bit set
            // Check if the integer is an int
            // and is signed
            if (($int & 0x18000000) == 0x18000000) {
                $int ^= 0x1fffffff;
                $int *= -1;
                $int -= 1;
            } else if (($int & 0x10000000) == 0x10000000) {
                // remove the signed flag
                $int &= 0x0fffffff;
            }

            return $int;

        }

        /**
         * readDate 
         * 
         * @return int 
         */
        public function readDate() {
            $dateref = $this->readU29();
            if (($dateref & 0x01) == 0) {
                $dateref = $dateref >> 1;
                if ($dateref>=count($this->storedObjects)) {
                    throw new Exception('Undefined date reference: ' . $dateref);
                    return false;
                }
                return $this->storedObjects[$dateref];
            }

            $timestamp = floor($this->stream->readDouble() / 1000);

            $dateTime = new DateTime('@' . $timestamp);
            
            $this->storedObjects[] = $dateTime;
            return $dateTime;
        }
        
         /**
         * readDSA
         * 
         * @return mixed
         */

        public function readDSA() {
            $obj = new SabreAMF_TypedObject("DSA", "");
            $vars = array();
            
            $flags = $this->readFlags();
            foreach($flags as $key => $flag){
                $bits = 0;
                if($key == 0){
                    if (($flag & 0x01) != 0){
                        $vars["body"] = $this->readAMFData();
                    }
                    if (($flag & 0x02) != 0){
                        $vars["clientId"] = $this->readAMFData();
                    }
                    if (($flag & 0x04) != 0){
                        $vars["destination"] = $this->readAMFData();
                    }
                    if (($flag & 0x08) != 0){
                        $vars["headers"] = $this->readAMFData();
                    }
                    if (($flag & 0x10) != 0){
                        $vars["messageId"] = $this->readAMFData();
                    }
                    if (($flag & 0x20) != 0){
                        $vars["timeStamp"] = $this->readAMFData();
                    }
                    if (($flag & 0x40) != 0){
                        $vars["timeToLive"] = $this->readAMFData();
                    }
                    $bits = 7;
                }
                elseif($key == 1){
                    if (($flag & 0x01) != 0){
                        $this->stream->readByte();
                        $temp = $this->readByteArray()->getData();
                        $vars["clientIdBytes"] = $temp;
                        $vars["clientId"] = $this->byteArrayToID($temp);
                    }
                    if (($flag & 0x02) != 0){
                        $this->stream->readByte();
                        $temp = $this->readByteArray()->getData();
                        $vars["messageIdBytes"] = $temp;
                        $vars["messageId"] = $this->byteArrayToID($temp);
                    } 
                    $bits = 2;
                }
                $vars[] = $this->readRemaining($flag, $bits);
            }
            
            $flags = $this->readFlags();
            foreach($flags as $key => $flag){
                $bits = 0;
                if($key == 0){
                    if (($flag & 0x01) != 0){
                        $vars["correlationId"] = $this->readAMFData();
                    }
                    if (($flag & 0x02) != 0){
                        $this->stream->readByte();
                        $temp = $this->readByteArray();
                        $vars["correlationIdBytes"] = $temp;
                        $vars["correlationId"] = $this->byteArrayToID($temp);
                    }
                    $bits = 2;
                }
                $vars[] = $this->readRemaining($flag, $bits);
            }
            $obj->setAMFData($vars);
            return $obj;
        }
 
        /**
         * readDSK
         * 
         * @return mixed
         */

        public function readDSK() {
            $ret = $this->readDSA();
            $ret->setAMFClassName("DSK");
            
            $data = $ret->getAMFData();
            
            $flags = $this->readFlags();
            foreach($flags as $flag){
                $data[] = $this->readRemaining($flag, 0);
            }
            
            return $ret;
        }
        
        /**
         * readFlags
         * 
         * @return array
         */
        private function readFlags(){
            $flags = array();
            
            do {
                $flag = $this->stream->readByte();
                $flags[] = $flag;
            }while(($flag & 0x80) != 0);
            
            return $flags;
        }

        /**
         * byteArrayToID
         * 
         * @return array
         */
        private function byteArrayToID($ba){
            $str = "";
            for($i=0; $i<strlen($ba);$i++){
                $int = ord($ba[$i]);
                if($i == 4 || $i == 6 || $i == 8 || $i == 10){
                    $str .= "-";
                }
                $str .= dechex($int);
            }
            return $str;
        }
        
        private function readRemaining($flag, $bits){
            $data = "";
            if(($flag >> $bits) != 0){
                for($i = $bits; $i < 6; $i++){
                    if((($flag >> $i) & 1) != 0){
                        $this->readAMFData();
                    }
                }
            }
            return $data;
        }
    }


