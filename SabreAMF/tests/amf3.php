<?php
require_once 'PHPUnit/Framework.php';

require_once 'tests/amf3/int.php';

class AMF3_Tests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('AMF3');

        $suite->addTestSuite('Test_AMF3_int');

        return $suite;

    }

}
?>
