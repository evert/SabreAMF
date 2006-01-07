<?

  header('Content-Type: text/plain');

  error_reporting(E_ALL | E_STRICT);

  require_once 'amf/Request.php';
  require_once 'amf/Server.php';


  $request = new SabreAMFRequest();
  $request->unserialize(file_get_contents('test.amf'));

  $server = new SabreAMFServer();
  $server->request = $request;
  $server->parseHeaders();

  $response = $server->getResponse();

  foreach($server->getRequest()->getCalls() as $call) {

        $result = array(
            'responseURI' => $call['response'],
            'body'        => $call['target'],
        );
        $response->addResult($result);

  }
  $server->sendResponse();

?>
