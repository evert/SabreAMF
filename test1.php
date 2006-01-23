<?php

    header('Content-Type: text/plain');

    include 'AMF/InputStream.php';
    include 'AMF/Message.php';


    $stream = new SabreAMF_InputStream(file_get_contents('dumps/91f297d1bc69b22d0e3afb23fd30bd3a'));

    $request = new SabreAMF_Message();

    $request->deserialize($stream);

    $data = $request->getBodies();

    print_r($data);

    echo(date('r',$data[0]['data'][2]));

?>
