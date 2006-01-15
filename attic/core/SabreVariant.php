<?php

    abstract class SabreVariant {

        private $explicitType;
        private $data;

        function __construct($data,$explicitType=false) {

            $this->data = $data;
            $this->explicitType = ($explicitType)?$explicitType:getType($data); 

        }

        function getData() {

            return $this->data; 

        }

        function getType() {
            return $this->explicitType;
        }

        function __toString() {

            return $data;

        }


    }

?>
