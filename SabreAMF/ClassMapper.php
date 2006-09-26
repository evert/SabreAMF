<?php

    /**
     * SabreAMF_ClassMapper 
     * 
     * @package SabreAMF 
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@rooftopsolutions.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    require_once 'SabreAMF/AMF3/RemotingMessage.php';
    require_once 'SabreAMF/AMF3/CommandMessage.php';
    require_once 'SabreAMF/AMF3/AcknowledgeMessage.php';
    require_once 'SabreAMF/AMF3/ErrorMessage.php';
    
    final class SabreAMF_ClassMapper {

        /**
         * @var array
         */
        static public $maps = array(
            'flex.messaging.messages.RemotingMessage'    => 'SabreAMF_AMF3_RemotingMessage',
            'flex.messaging.messages.CommandMessage'     => 'SabreAMF_AMF3_CommandMessage',
            'flex.messaging.messages.AcknowledgeMessage' => 'SabreAMF_AMF3_AcknowledgeMessage',
            'flex.messaging.messages.ErrorMessage'       => 'SabreAMF_AMF3_ErrorMessage',
        );

        /**
         * Assign this callback to intercept calls to getLocalClass
         *
         * @var callback
         */
        static public $onGetLocalClass;

        /**
         * Assign this callback to intercept calls to getRemoteClass
         *
         * @var callback
         */
        static public $onGetRemoteClass;

        /**
         * The Constructor
         * 
         * We make the constructor private so the class cannot be initialized
         * 
         * @return void
         */
        private function __construct() { }

        /**
         * Register a new class to be mapped 
         * 
         * @param string $remoteClass 
         * @param string $localClass 
         * @return void
         */
        static public function registerClass($remoteClass,$localClass) {

            self::$maps[$remoteClass] = $localClass;

        }

        /**
         * Get the local classname for a remote class 
         *
         * This method will return FALSE when the class is not found
         * 
         * @param string $remoteClass 
         * @return mixed 
         */
        static public function getLocalClass($remoteClass) {

            if (is_callable(self::$onGetLocalClass)) {
                $localClass = call_user_func(self::$onGetLocalClass,$remoteClass);
                if ($localClass) return $localClass;
            }
            return (isset(self::$maps[$remoteClass]))?self::$maps[$remoteClass]:false;

        }

        /**
         * Get the remote classname for a local class 
         * 
         * This method will return FALSE when the class is not found
         * 
         * @param string $localClass 
         * @return mixed 
         */
        static public function getRemoteClass($localClass) {

            if (is_callable(self::$onGetRemoteClass)) {
                $remoteClass = call_user_func(self::$onGetRemotelass,$localClass);
                if ($remoteClass) return $remoteClass;
            }
            return array_search($localClass,self::$maps);

        }

    }

?>
