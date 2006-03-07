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
         * @param mixed $settype 
         * @return mixed 
         */
        public abstract function readAMFData($settype = null); 


   }

?>
