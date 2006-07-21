<?php

    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    class SabreAMF_AMF3_CommandMessage extends SabreAMF_AMF3_AbstractMessage {

        public $operation;
        public $messageRefType;
        public $correlationId;

    }

?>
