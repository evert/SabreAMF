<?php

    class SabreAMF_ByteArray {

        private $data;

        function __construct($data = false) {

            $this->data = $data;

        }

        function getData() {

            return $this->data;

        }

        function setData() {

            $this->data = $data;

        }

    }

?>
