<?php

    /**
     * SabreAMF_ITypedObject 
     *
     * This interface can be used to encode your data with a specified classname. The result will be that the flash/flex client will transform the data to an object of the specified classname
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    interface SabreAMF_ITypedObject {

        /**
         * getAMFClassName 
         *
         * This method should return the classname as it should show up for the client
         * 
         * @return string 
         */
        public function getAMFClassName();

        /**
         * getAMFData 
         *
         * This method should return the actual contents of the object that should be encoded
         * 
         * @return mixed 
         */
        public function getAMFData();

    }

?>
