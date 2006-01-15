<?php

    include 'AMF/Server.php';

    $server = new SabreAMF_Server(dirname(__FILE__) . '/dumps/');
    $requests = $server->getRequests();
   
    foreach($requests as $request) {

        $server->setResponse($request['response'],SabreAMF_Const::R_RESULT,$request['data']); 

    }

    $server->sendResponse();

?>
