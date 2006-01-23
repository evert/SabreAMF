<?php

    $data = $GLOBALS['HTTP_RAW_POST_DATA'];
    file_put_contents('/home/evert/dev/sabreamf/dumps/',md5($data),$data);

?>
