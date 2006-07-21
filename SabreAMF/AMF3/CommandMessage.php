<?php

    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    class SabreAMF_AMF3_CommandMessage extends SabreAMF_AMF3_AbstractMessage {

        const SUBSCRIBE_OPERATION          = 0;
        const UNSUSBSCRIBE_OPERATION       = 1;
        const POLL_OPERATION               = 2;
        const CLIENT_SYNC_OPERATION        = 4;
        const CLIENT_PING_OPERATION        = 5;
        const CLUSTER_REQUEST_OPERATION    = 7; 
        const LOGIN_OPERATION              = 8;
        const LOGOUT_OPERATION             = 9;
        const SESSION_INVALIDATE_OPERATION = 10;

        public $operation;
        public $messageRefType;
        public $correlationId;

    }

?>
