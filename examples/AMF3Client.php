<?php

    /* $Id$ */

    include 'SabreAMF/Client.php'; //Include the client scripts
 
    $client = new SabreAMF_Client('http://localhost/server.php'); // Set up the client object
  
    $result = $client->sendRequest('myService.myMethod',new SabreAMF_AMF3_Wrapper(array('myParameter'))); //Send a request to myService.myMethod and send as only parameter 'myParameter'
   
    var_dump($result); //Dump the results


