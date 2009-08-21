<?php

if (!is_readable('CallbackServer.php')) {
  printf("\nThis script should be invoked from the root of the project!\n\n");
  die(1);
}

$inc = getcwd().'/../'.PATH_SEPARATOR.ini_get('include_path');
ini_set('include_path', $inc);
var_dump($inc);

require_once 'PHPUnit/Framework.php';
 
require_once 'tests/amf3.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('SabreAMF');
 
        $suite->addTestSuite('AMF3_Tests');
 
        return $suite;
    }
}
?>
