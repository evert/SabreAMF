<?

  header('Content-Type: application/x-amf');

  error_reporting(E_ALL | E_STRICT);

  require_once 'amf/Server.php';

  $server = new SabreAMFServer();
  $server->parseRequest();

  $response = $server->getResponse();

  foreach($server->getRequest()->getCalls() as $call) {

        $result = array(
            'responseURI' => $call['response'] . '/onResult',
            'body'        => $call['target'],
        );
        $response->addResult($result);

  }
  $server->sendResponse();

?>
