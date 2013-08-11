<?php

require_once 'PHPUnit/Framework.php';
require_once 'InputStream.php';
require_once 'OutputStream.php';
require_once 'AMF3/Deserializer.php';
require_once 'AMF3/Serializer.php';

class Test_AMF3_Int extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerInt
     */
    public function testRead($input, $value)
    {
        $amfInputStream  = new SabreAMF_InputStream($input);
        $amfDeserializer = new SabreAMF_AMF3_Deserializer($amfInputStream);
        $deserialized    = $amfDeserializer->readAMFData();

        $this->assertEquals($value, $deserialized);
    }

    /**
     * @dataProvider providerInt
     */
    public function testWrite($input, $value, $writebytetest)
    {
        $amfOutputStream = new SabreAMF_OutputStream($input);
        $amfSerializer   = new SabreAMF_AMF3_Serializer($amfOutputStream);
        $amfSerializer->writeAMFData($value);
        $serialized = $amfOutputStream->getRawData();

        $this->testRead($serialized, $value);

        if ($writebytetest) {
            $this->assertEquals($serialized, $input);
        }
    }

    public function providerInt()
    {

        return array(
                  array(
                      "\x04\xb7\xfd\x98\x68",
                      234788968,
                      true
                  ), array(
                      "\x04\xbf\xff\xff\xff",
                      268435455,
                      true
                  ), array(
                      "\x04\x00",
                      0,
                      true
                  ), array(
                      "\x04\xff\xff\xff\xff",
                      -1,
                      true
                  ), array(
                      // signed positive
                      "\x04\xC0\xA4\xB4\x56",
                      1193046,
                      false // encodes as uint
                  ), array(
                      "\x04\xC8\xE8\x56",
                      1193046,
                      true
                  ), array(
                      "\x04\xA4\xB4\x56",
                      596566,
                      true
                  ), array(
                      "\x04\xe0\x80\x80\x00",
                      -134217728,
                      true
                  ), array(
                      "\x04\xbf\xff\xff\xff",
                      268435455,
                      true
                  ), array(
                      "\x04\xbf\xff\x7f",
                      1048575,
                      true
                  ), array(
                      "\x04\xbf\x7f",
                      8191,
                      true
                  ), array(
                      "\x04\x3f",
                      63,
                      true
                  )
               );

    }

}
?>
