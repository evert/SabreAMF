<?php

    require_once dirname(__FILE__) . '/TypedObject.php'; 
 
    /**
     * SabreAMF_RecordSet 
     * 
     * @uses SabreAMF_TypedObject
     * @uses Countable
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     */
    abstract class SabreAMF_RecordSet implements SabreAMF_TypedObject, Countable {


        /**
         * getData 
         * 
         * @return array 
         */
        abstract public function getData(); 

        /**
         * getColumnNames 
         * 
         * @return array 
         */
        abstract public function getColumnNames();

        /**
         * getAMFClassName 
         * 
         * @return string 
         */
        final public function getAMFClassName() {

            return 'RecordSet';

        }

        /**
         * getAMFData 
         * 
         * @return object 
         */
        public function getAMFData() {

            return (object)array(
                'ServerInfo' => (object)array(
                    'totalCount'  => $this->count(),
                    'initialData' => $this->getData(),
                    'cursor'      => 1,
                    'serviceName' => false,
                    'columnNames' => $this->getColumnNames(),
                    'version'     => 1,
                    'id'          => false,
                )
            );


        }

    }



?>
