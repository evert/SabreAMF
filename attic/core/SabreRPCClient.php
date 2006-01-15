<?php


  abstract class SabreRPCClient {

      private $endPoint;

      abstract function request();
       
      function setEndPoint($endPoint) {
            $this->endPoint = $endPoint;
      }

      function getEndPoint() {
            return $this->endPoint;
      }
          


  }

?>
