<?php

    /* $Id: server.php 1218 2006-03-07 23:07:44Z evert $ */

    // Include the server class
    include 'SabreAMF/CallbackServer.php';


    function myCallBack($service,$method,$data) {
        
        return 'hello world';

    }


    // Init server 
    $server = new SabreAMF_CallbackServer();

    $server->onInvokeService = 'myCallBack';

    $server->exec();


