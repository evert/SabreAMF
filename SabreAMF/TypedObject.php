<?php

    /**
     * SabreAMF_TypedObject 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    interface SabreAMF_TypedObject {

        /**
         * getAMFClassName 
         * 
         * @return void
         */
        public function getAMFClassName();
        /**
         * getAMFData 
         * 
         * @return void
         */
        public function getAMFData();

    }

?>
