<?php

    /**
     * SabreAMF_AMF3_AcknowledgeMessage 
     * 
     * @uses SabreAMF_AMF3_AbstractMessage
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id$
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@rooftopsolutions.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    /**
     * This message is based on Abstract Message
     */
    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    /**
     * This is the receipt for any message thats being sent
     */
    class SabreAMF_AMF3_AcknowledgeMessage extends SabreAMF_AMF3_AbstractMessage {

        /**
         * The ID of the message where this is a receipt of 
         * 
         * @var string 
         */
        public $correlationId;

    }

?>
