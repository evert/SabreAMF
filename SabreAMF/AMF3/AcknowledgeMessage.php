<?php

    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    class SabreAMF_AMF3_AcknowledgeMessage extends SabreAMF_AMF3_AbstractMessage {

        public $correlationId;

    }

?>
