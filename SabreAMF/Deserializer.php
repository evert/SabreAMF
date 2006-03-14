<?php


    /**
     * SabreAMF_Deserializer 
     * 
     * This is the abstract Deserializer. The AMF0 and AMF3 classes descent from this class
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    abstract class SabreAMF_Deserializer {

        /**
         * stream 
         * 
         * @var SabreAMF_InputStream
         */
        protected $stream;

        /**
         * __construct 
         *
         * @param SabreAMF_InputStream $stream 
         * @return void
         */
        public function __construct(SabreAMF_InputStream $stream) {

            $this->stream = $stream;

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


   }

?>
