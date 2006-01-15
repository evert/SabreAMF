<?php

    include 'AMF/Request.php';
    include 'AMF/InputStream.php';
    include 'AMF/Deserializer.php';


    $stream = new SabreAMF_InputStream(file_get_contents('test.amf'));

    $request = new SabreAMF_Request();

    $request->deserialize($stream);

?>
