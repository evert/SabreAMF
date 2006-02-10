<?php

    /* $Id$ */

    require_once dirname(__FILE__) . '/TypedObject.php'; 
 
    abstract class SabreAMF_RecordSet implements SabreAMF_TypedObject, Countable {

        abstract public function getData(); 

        abstract public function getColumnNames();

        final public function getAMFClassName() {

            return 'RecordSet';

        }

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
