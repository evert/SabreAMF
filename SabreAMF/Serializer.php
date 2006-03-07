<?php


    /**
     * SabreAMF_Serializer 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    abstract class SabreAMF_Serializer {

        /**
         * stream 
         * 
         * @var SabreAMF_OutputStream 
         */
        protected $stream;

        /**
         * __construct 
         * 
         * @param SabreAMF_OutputStream $stream 
         * @return void
         */
        public function __construct(SabreAMF_OutputStream $stream) {

            $this->stream = $stream;

        }

        /**
         * writeAMFData 
         * 
         * @param mixed $data 
         * @param int $forcetype 
         * @return mixed 
         */
        public abstract function writeAMFData($data,$forcetype=null); 

        /**
         * getStream
         *
         * @return SabreAMF_OutputStream
         */
        public function getStream() {

            return $this->stream;

        }

    }

?>
