<?php


  abstract class SabreRPCServer {

      public $request;
      protected $response;
      private $credUser;
      private $credPass;

      public function getCredentials() {
            return array($this->credUser,$this->credPass);
      }

      protected function setCredentials($user,$pass) {

          $this->credUser = $user;
          $this->credPass = $pass;

      }

      abstract function parseRequest();
      abstract function sendResponse();

      public function getRequest() {
          return $this->request;
      }

      public function getResponse() {
          return $this->response;
      }


  }

?>
