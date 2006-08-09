<?php

    /**
     * SabreAMF_AMF3_RemotingMessage 
     * 
     * @uses SabreAMF_AM3_AbstractMessage
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@rooftopsolutions.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    /**
     * Invokes a message on a service
     */
    class SabreAMF_AMF3_RemotingMessage extends SabreAMF_AMF3_AbstractMessage {

        /**
         * operation 
         * 
         * @var string 
         */
        public $operation;

        /**
         * source 
         * 
         * @var string 
         */
        public $source;

    }

?>
