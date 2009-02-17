<?php

include 'SabreAMF/Client.php';

// change this to whatever drupal instalation path is on your
// development/production webserver.
// this test only sends and receives back what it sends from a
// predefined sabreamf_pingme service method.
// it behaves like a normal flash/flex application
// (useful when crossdomain.xml madness is above your pattience)

$gateway_url = 'http://localhost/drupal/services/sabreamf';

$sabreamf = new SabreAMF_Client($gateway_url);

$object = new stdClass;
$object->int_value = 10;
$object->float_value = 1.123456;
$object->datetime_value = date_create(date('Y-m-d'));
$object->string_value = 'this is a string';
$object->boolean_value = TRUE;
$object->array_value = array('a', 'b', 'c');
$parameter = array(
    'int_value' => 10,
    'float_value' => 1.123456,
    'datetime_value' => date_create(date('Y-m-d')),
    'string_value' => 'weee',
    'boolean_value' => TRUE,
    'array_value' => array('a', 'b', 'c'),
    'object_value' => $object
);

try {
    $sabreamf_result = $sabreamf->sendRequest('sabreamf_server.sabreamf_pingme',new SabreAMF_AMF3_Wrapper(array($parameter)));
    var_dump($sabreamf_result);
} catch (Exception $ex) {
    echo "\n  ERROR - $gateway_url - may not point to a valid gateway.\n\n  ";
    echo $ex->getMessage()."\n";
}
