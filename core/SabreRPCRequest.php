<?

    require_once(dirname(__FILE__) . '/SabreRPCMessage.php');
    
    abstract class SabreRPCRequest implements SabreRPCMessage {

        abstract function getCalls();

    }


?>
