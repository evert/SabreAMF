<?php

    /**
     * SabreAMF_Deserializer 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */


    /**
     * SabreAMF_Deserializer 
     * 
     * This is the abstract Deserializer. The AMF0 and AMF3 classes descent from this class
     */
    abstract class SabreAMF_Deserializer {

        /**
         * stream 
         * 
         * @var SabreAMF_InputStream
         */
        protected $stream;

        /**
         * savedreferences
         * 
         * @var array
         */
        protected $savedreferences;

        /**
         * __construct 
         *
         * @param SabreAMF_InputStream $stream 
         * @return void
         */
        public function __construct(SabreAMF_InputStream $stream, $savedRefs = array()) {

            $this->stream = $stream;
            $this->savedreferences = $savedRefs;

        }

        /**
         * readAMFData 
         * 
         * Starts reading an AMF block from the stream
         * 
         * @param mixed $settype 
         * @return mixed 
         */
        public abstract function readAMFData($settype = null); 


        /**
         * getLocalClassName 
         * 
         * @param string $remoteClass 
         * @return mixed 
         */
        protected function getLocalClassName($remoteClass) {

            return SabreAMF_ClassMapper::getLocalClass($remoteClass);

        } 

   }


