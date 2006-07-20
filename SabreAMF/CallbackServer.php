<?php

    require_once(dirname(__FILE__) . '/Server.php');


    /**
     * AMF Server
     * 
     * This is the AMF0/AMF3 Server class. Use this class to construct a gateway for clients to connect to 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     * @uses SabreAMF_Server
     * @uses SabreAMF_Message
     * @uses SabreAMF_Const
     */
    class SabreAMF_CallbackServer {

        /**
         * classpaths 
         * 
         * @var array
         */
        private $classpaths = array();

        /**
         * Register a new Service
         *
         * @param string $path Service Path
         * @param mixed $object either an object, or a class
         */
        public function registerService($path,$object) { 

            $this->classpaths[$path] = $object;

        }

        /**
         * callService 
         * 
         * @param string $service Service name 
         * @param string $method Method name
         * @return void
         */
        protected function callService($service,$method) {

            //TODO : Well i still need to do this part

        }

        /**
         * execute 
         * 
         * @return void
         */
        public function execute() {

            foreach($this->getRequests() as $request) {

                $service = substr($request['target'],0,strrpos($request['target'],'.'));
                $method  = substr(strrchr($request['target'],'.'),1);
               
                try {
                    $data = $this->callService($service,$method);
                    $status = SabreAMF_Const::R_RESULT;
                } catch (Exception $e) {
                    $data = array(
                        'description' => $e->getMessage(),
                        'details'     => false,
                        'line'        => $e->getLine(), 
                        'code'        => $e->getCode(),
                    );
                    $status = SabreAMF_Const::R_STATUS;
                }

                $this->setResponse($request['response'],$status,$data);

            }
            $this->sendResponse();

        }

    }

?>
