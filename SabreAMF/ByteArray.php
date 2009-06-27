<?php

    /**
     * SabreAMF_ByteArray 
     * 
     * @package SabreAMF
     * @version $Id$
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     * @license licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     */
    class SabreAMF_ByteArray {

        /**
         * data 
         * 
         * @var string 
         */
        private $data;

        /**
         * __construct 
         * 
         * @param string $data 
         * @return void
         */
        function __construct($data = '') {;

            $this->data = $data;

        }

        /**
         * getData 
         * 
         * @return string 
         */
        function getData() {

            return $this->data;

        }

        /**
         * setData 
         * 
         * @param string $data
         * @return void
         */
        function setData($data) {

            $this->data = $data;

        }

    }


