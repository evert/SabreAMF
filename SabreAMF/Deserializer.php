<?php

    /**
     * SabreAMF_Deserializer 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    /**
     * We need the classmapper
     */
    require_once 'SabreAMF/ClassMapper.php';


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

?>
