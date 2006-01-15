<?php

    include 'AMF/Client.php';

    $client = new SabreAMF_Client('http://www.filemobile.com/services/amf'); // Set up the client object
 
    $result = $client->sendRequest('profiles.getList',array(''));
   
    var_dump($result);
?>
