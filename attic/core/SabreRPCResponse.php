<?

    require_once(dirname(__FILE__) . '/SabreRPCMessage.php');
    
    abstract class SabreRPCResponse implements SabreRPCMessage {

        abstract function addResult($result);

    }


?>
